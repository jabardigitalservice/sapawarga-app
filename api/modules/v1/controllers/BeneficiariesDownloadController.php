<?php

namespace app\modules\v1\controllers;

use app\models\Area;
use app\models\Beneficiary;
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
                $query_params['domicile_kec_bps_id'][$null_value_pos] = null;
            }
        }

        // export bnba
        $id = Yii::$app->queue->push(new ExportBeneficiariesJob([
            'params' => $query_params,
            'user_id' => $user->id,
        ]));

        return [ 'job_id' => $id ];
    }
}
