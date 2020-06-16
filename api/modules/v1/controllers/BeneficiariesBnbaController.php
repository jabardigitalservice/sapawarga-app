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
            'only' => ['download'],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['download'],
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

    public function actionDownload()
    {
        $params = Yii::$app->request->getQueryParams();
        $query_params = [];

        $user = Yii::$app->user;
        $authUserModel = $user->identity;

        if ($user->can('staffKabkota')) {
            $parent_area = Area::find()->where(['id' => $authUserModel->kabkota_id])->one();
            $query_params['kode_kab'] = $parent_area->code_bps;
            if (isset($params['kode_kec'])) {
                $query_params['kode_kec'] = explode(',', $params['kode_kec']);
            }
        } elseif ($user->can('staffProv') || $user->can('admin')) {
            if (isset($params['kode_kec'])) {
                $query_params['kode_kec'] = explode(',', $params['kode_kec']);
            }
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

        // handler utk row dengan kolom kode_kec kosong
        if (isset($query_params['kode_kec'])) {
            $null_value_pos = array_search('0', $query_params['kode_kec']);
            if ($null_value_pos !== false) {
                $query_params['kode_kec'][$null_value_pos] = null;
            }
        }

        // export bnba
        $id = Yii::$app->queue->ttr(30 * 60)->push(new ExportBnbaJob([
            'params' => $query_params,
            'user_id' => $user->id,
        ]));

        return [ 'job_id' => $id ];
    }

    public function actionMonitoring()
    {
        $user = Yii::$app->user;

        $params = Yii::$app->request->getQueryParams();

        // EXIST query ref: https://stackoverflow.com/a/10688065
        $exist_subquery = <<<SQL
            EXISTS(
                SELECT 1 
                FROM beneficiaries_bnba_tahap_1 
                WHERE beneficiaries_bnba_tahap_1.kode_kab = areas.code_bps
                  %s # custom query utk is_dtks
                LIMIT 1
            )
SQL;
        $query = (new Query())
          ->select([
            'id',
            'name',
            'code_bps',
            'dtks_exist'     => sprintf($exist_subquery, 'AND is_dtks = 1'),
            'non-dtks_exist' => sprintf($exist_subquery, 'AND (is_dtks != 1 OR is_dtks IS NULL)'),
          ])
          ->from('areas')
          ->where(['areas.depth' => 2 ])
          ;

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
                if ($row[$type . '_exist']) {
                    $final_rows[] = [
                        'id' => $row['id'],
                        'name' => $row['name'],
                        'code_bps' => $row['code_bps'],
                        'type' => $type,
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
