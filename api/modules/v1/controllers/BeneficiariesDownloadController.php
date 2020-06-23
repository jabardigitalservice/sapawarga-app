<?php

namespace app\modules\v1\controllers;

use app\models\Area;
use app\models\Beneficiary;
use app\models\BansosBeneficiariesDownloadHistory;
use Yii;
use yii\db\Query;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use Jdsteam\Sapawarga\Jobs\ExportBeneficiariesJob;

/**
 * BeneficiariesBnbaTahapSatuController implements the CRUD actions for BeneficiaryBnbaTahapSatu model.
 */
class BeneficiariesDownloadController extends ActiveController
{
    public $modelClass = Beneficiary::class;

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
            $query_params['domicile_kabkota_bps_id'] = $parent_area->code_bps;
            if (isset($params['kode_kec'])) {
                $query_params['domicile_kec_bps_id'] = explode(',', $params['kode_kec']);
            }
        } elseif ($user->can('staffProv') || $user->can('admin')) {
            if (isset($params['kode_kec'])) {
                $query_params['domicile_kec_bps_id'] = explode(',', $params['kode_kec']);
            }
            if (isset($params['kode_kab'])) {
                $query_params['domicile_kabkota_bps_id'] = explode(',', $params['kode_kab']);
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
        }

        // handler utk row dengan kolom kode_kec kosong
        if (isset($query_params['domicile_kec_bps_id'])) {
            $null_value_pos = array_search('0', $query_params['domicile_kec_bps_id']);
            if ($null_value_pos !== false) {
                // replace 0 with '' and null
                unset($query_params['domicile_kec_bps_id'][$null_value_pos]);
                array_push($query_params['domicile_kec_bps_id'], '', null);
            }
        }

        $job_history = new BansosBeneficiariesDownloadHistory;
        $job_history->user_id = $user->id;
        $job_history->params = $query_params;
        $job_history->row_count = $job_history->countAffectedRows();
        $job_history->save();

        // export bnba
        $id = Yii::$app->queue->push(new ExportBeneficiariesJob([
            'params' => $query_params,
            'user_id' => $user->id,
            'history_id' => $job_history->id,
        ]));

        return [ 
          'history_id' => $job_history->id,
        ];
    }

    public function actionDownloadStatus($history_id=null)
    {
        if ($history_id != null) {
            $result = BansosBeneficiariesDownloadHistory::findOne($history_id);
            if (empty($result)) {
                throw new NotFoundHttpException();
            } else {
                return ArrayHelper::toArray($result, [
                  'app\models\BansosBeneficiariesDownloadHistory' => array_keys($result->fields()) + [
                      'aggregate' => function($job_history) {
                          return $job_history->getAggregateRowProgress();
                      },
                      'waiting_jobs' => function($job_history) {
                          return $job_history->countJobInLine();
                      },
                  ],
                ]);
            }
        } else {
            $user = Yii::$app->user;

            return BansosBeneficiariesDownloadHistory::find()->where([
                'user_id' => $user->id, 
            ])->all();
        }
    }
}
