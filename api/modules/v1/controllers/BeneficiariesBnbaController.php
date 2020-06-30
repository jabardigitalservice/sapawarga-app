<?php

namespace app\modules\v1\controllers;

use app\models\Area;
use app\models\BansosBnbaDownloadHistory;
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
use yii\web\NotFoundHttpException;
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
            'only' => ['index', 'view', 'download', 'summary'],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['monitoring'],
                    'roles' => ['admin', 'staffProv'],
                ],
                [
                    'allow' => true,
                    'actions' => ['index', 'view', 'download', 'download-status', 'summary'],
                    'roles' => ['admin', 'staffProv', 'staffKabkota', 'staffKec', 'staffKel'],
                ],
            ],
        ];

        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();

        // Override Actions
        unset($actions['create']);
        unset($actions['update']);
        unset($actions['delete']);

        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

        return $actions;
    }

    public function actionSummary()
    {
        $params = Yii::$app->request->getQueryParams();
        $params = $this->getAreaByUser($params);

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
            $data[$val] = 0;
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

        $job_history = new BansosBnbaDownloadHistory;
        $job_history->user_id = $user->id;
        $job_history->params = $query_params;
        $job_history->row_count = $job_history->countAffectedRows();
        $job_history->save();

        // export bnba
        $id = Yii::$app->queue->push(new ExportBnbaJob([
            'params' => $query_params,
            'userId' => $user->id,
            'historyId' => $job_history->id,
        ]));

        $job_history->job_id = $id;
        $job_history->save();

        return [
          'history_id' => $job_history->id,
        ];
    }

    public function actionDownloadStatus($history_id = null)
    {
        if ($history_id != null) {
            $result = BansosBnbaDownloadHistory::findOne($history_id);
            if (empty($result)) {
                throw new NotFoundHttpException();
            } else {
                return $result;
            }
        } else {
            $user = Yii::$app->user;
            $params = Yii::$app->request->getQueryParams();

            $query = BansosBnbaDownloadHistory::find()->where([
                'user_id' => $user->id,
            ]);

            $sortOrder = (Arr::get($params, 'order', null) == 'asc') ? SORT_ASC : SORT_DESC;
            return new \yii\data\ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => Arr::get($params, 'limit', 10),
                ],
                'sort' => [
                    'defaultOrder' => [
                        'id' => $sortOrder,
                    ]
                ],
            ]);
        }
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

    public function prepareDataProvider()
    {
        $params = Yii::$app->request->getQueryParams();
        $params = $this->getAreaByUser($params);

        $user = Yii::$app->user;
        $authUserModel = $user->identity;
        $search = new BeneficiaryBnbaTahapSatuSearch();
        $search->userRole = $authUserModel->role;

        return $search->search($params);
    }

    public function getAreaByUser($params)
    {
        $user = Yii::$app->user;
        $authUserModel = $user->identity;

        $search = new BeneficiaryBnbaTahapSatuSearch();
        $search->userRole = $authUserModel->role;

        if ($user->can('staffKabkota')) {
            $area = Area::find()->where(['id' => $authUserModel->kabkota_id])->one();
            $params['kode_kab'] = $area->code_bps;
        } elseif ($user->can('staffKec')) {
            $area = Area::find()->where(['id' => $authUserModel->kabkota_id])->one();
            $params['kode_kab'] = $area->code_bps;
            $area = Area::find()->where(['id' => $authUserModel->kec_id])->one();
            $params['kode_kec'] = $area->code_bps;
        } elseif ($user->can('staffKel') || $user->can('staffRW') || $user->can('trainer')) {
            $area = Area::find()->where(['id' => $authUserModel->kabkota_id])->one();
            $params['kode_kab'] = $area->code_bps;
            $area = Area::find()->where(['id' => $authUserModel->kec_id])->one();
            $params['kode_kec'] = $area->code_bps;
            $area = Area::find()->where(['id' => $authUserModel->kel_id])->one();
            $params['kode_kel'] = $area->code_bps;
            $params['rw'] = $authUserModel->rw;
        }

        return $params;
    }

}
