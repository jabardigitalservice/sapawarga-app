<?php

namespace app\modules\v1\controllers;

use app\models\Area;
use app\models\BeneficiaryBnbaTahapSatu;
use app\models\BeneficiaryBnbaTahapSatuSearch;
use app\validator\NikRateLimitValidator;
use app\validator\NikValidator;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Yii;
use yii\db\Query;
use yii\data\ArrayDataProvider;
use yii\base\DynamicModel;
use yii\filters\AccessControl;
use yii\web\HttpException;
use yii\web\ForbiddenHttpException;
use yii\helpers\ArrayHelper;
use Illuminate\Support\Collection;
use Illuminate\Support\Arr;
use Jdsteam\Sapawarga\Jobs\ExportBnbaJob;

/**
 * BeneficiariesBnbaTahapSatuController implements the CRUD actions for BeneficiaryBnbaTahapSatu model.
 */
class BeneficiariesBnbaController extends ActiveController
{
    public $modelClass = BeneficiaryBnbaTahapSatu::class;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        return $this->behaviorCors($behaviors);
    }

    protected function behaviorAccess($behaviors)
    {
        // setup access
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => ['download', 'summary'],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['download', 'summary'],
                    'roles' => ['admin', 'staffProv', 'staffKabkota', 'staffKec'],
                ],
                [
                    'allow' => true,
                    'actions' => ['monitoring'],
                    'roles' => ['admin', 'staffProv'],
                ]
            ],
        ];

        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();

        // Override Actions
        unset($actions['create']);
        unset($actions['view']);
        unset($actions['update']);
        unset($actions['delete']);

        return $actions;
    }

    public function actionSummary()
    {
        $params = Yii::$app->request->getQueryParams();
        $kodeKab = Arr::get($params, 'kode_kab');
        $tahap = Arr::get($params, 'tahap');

        // $type is empty means API call from homepage
        if (empty($kodeKab)) {
            $search =  (new \yii\db\Query())
                ->select(['id_tipe_bansos', 'SUM(total) AS total'])
                ->from('beneficiaries_bnba_statistic_type')
                ->where(['tahap_bantuan' => $tahap])
                ->groupBy(['id_tipe_bansos'])
                ->all();
        } else {
            $search = new BeneficiaryBnbaTahapSatuSearch();
            $search = $search->getSummaryByType($params);
        }

        // Reformat result
        $beneficiaryTypes = [
            '1' => Yii::t('app', 'type.beneficiaries.pkh'),
            '2' => Yii::t('app', 'type.beneficiaries.bnpt'),
            '3' => Yii::t('app', 'type.beneficiaries.bnpt_perluasan'),
            '4' => Yii::t('app', 'type.beneficiaries.bansos_tunai'),
            '5' => Yii::t('app', 'type.beneficiaries.bansos_presiden_sembako'),
            '6' => Yii::t('app', 'type.beneficiaries.bansos_provinsi'),
            '7' => Yii::t('app', 'type.beneficiaries.dana_desa'),
            '8' => Yii::t('app', 'type.beneficiaries.bansos_kabkota'),
        ];

        $data = [];
        foreach ($beneficiaryTypes as $key => $val) {
            foreach ($search as $value) {
                $data[$val] = ($key == $value['id_tipe_bansos']) ? intval($value['total']) : 0;

            }
        }

        return $data;
    }

    public function actionDownload()
    {
        $params = Yii::$app->request->getQueryParams();
        $query_params = [];

        $user = Yii::$app->user;
        $authUserModel = $user->identity;

        if (isset($params['tahap_bantuan'])) {
            $query_params['tahap_bantuan'] = explode(',', $params['tahap_bantuan']);
        } else {
            $data = (new \yii\db\Query())
            ->from('beneficiaries_current_tahap')
            ->all();

            if (count($data)) {
                $query_params['tahap_bantuan'] = $data[0]['current_tahap_bnba'];
            }
        }
        if ($user->can('staffKabkota')) {
            $parent_area = Area::find()->where(['id' => $authUserModel->kabkota_id])->one();
            $query_params['kode_kab'] = $parent_area->code_bps;
        } elseif ($user->can('staffProv') || $user->can('admin')) {
            if (isset($params['kode_kab'])) {
                $query_params['kode_kab'] = explode(',', $params['kode_kab']);
            }
            if (isset($params['bansos_type'])) {
                $bansos_type = explode(',', $params['bansos_type']);
                $is_dtks = [];
                if (in_array('dtks', $bansos_type)) {
                    $is_dtks[] = 1;
                }
                if (in_array('non-dtks', $bansos_type)) {
                    array_push($is_dtks, 0, null);
                }
                $query_params['is_dtks'] = $is_dtks;
            }
        } else {
            return 'Fitur download data BNBA tidak tersedia untuk user ini';
        }

        // export bnba
        $id = Yii::$app->queue->push(new ExportBnbaJob([
            'params' => $query_params,
            'user_id' => $user->id,
        ]));

        return [ 'job_id' => $id ];
    }

    public function actionMonitoring()
    {
        $user = Yii::$app->user;

        $params = Yii::$app->request->getQueryParams();

        $tahap_bantuan = '';
        if (isset($params['tahap_bantuan'])) {
            $tahap_bantuan = sprintf(' AND tahap_bantuan = %d', $params['tahap_bantuan']);
        } else {
            $data = (new \yii\db\Query())
            ->from('beneficiaries_current_tahap')
            ->all();

            if (count($data)) {
                $tahap_bantuan = sprintf(' AND tahap_bantuan = %d', $data[0]['current_tahap_bnba']);
            }
        }

        $last_updated_subquery = <<<SQL
            (SELECT MAX(updated_at)
            FROM bansos_bnba_upload_histories
            WHERE bansos_bnba_upload_histories.kabkota_code = areas.code_bps
              %s
            )
        SQL;
        $query = (new Query())
          ->select([
            'id',
            'name',
            'code_bps',
            'dtks_last_update'      => sprintf($last_updated_subquery, 'AND bansos_type > 50' . $tahap_bantuan),
            'non-dtks_last_update'  => sprintf($last_updated_subquery, 'AND bansos_type < 10' . $tahap_bantuan),
          ])
          ->from('areas')
          ->where(['areas.depth' => 2 ]);

        if (isset($params['kode_kab'])) {
            $query = $query->andWhere([ 'areas.code_bps' => explode(',', $params['kode_kab']) ]);
        }
        if (isset($params['bansos_type']) && !empty($params['bansos_type'])) {
            $params['bansos_type'] = explode(',', $params['bansos_type']);
        } else {
            $params['bansos_type'] = ['dtks','non-dtks'];
        }

        $final_rows = [];
        $rows = $query->all();
        foreach ($rows as $row) {
            foreach ($params['bansos_type'] as $type) {
                if ($row[$type . '_last_update'] != null) {
                    $final_rows[] = [
                        'id' => $row['id'],
                        'name' => $row['name'],
                        'code_bps' => $row['code_bps'],
                        'type' => $type,
                        'last_update' => $row[$type . '_last_update'],
                    ];
                }
            }
        }

        $pageLimit = Arr::get($params, 'limit', 10);
        $provider = new ArrayDataProvider([
            'allModels' => $final_rows,
            'pagination' => [
                'pageSize' => $pageLimit,
            ],
        ]);

        return $provider;
    }
}
