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
use yii\base\DynamicModel;
use yii\filters\AccessControl;
use yii\web\HttpException;
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
                    'roles' => ['admin', 'staffProv', 'staffKabkota', 'staffKec', 'staffKel', 'staffRW', 'trainer'],
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

        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

        return $actions;
    }

    public function actionDownload()
    {
        $params = Yii::$app->request->getQueryParams();

        $user = Yii::$app->user;
        $authUserModel = $user->identity;

        if ($user->can('staffKabkota')) {
            $parent_area = Area::find()->where(['id' => $authUserModel->kabkota_id])->one();
            $params['kode_kab'] = $parent_area->code_bps;
            if (isset($params['kode_kec'])) {
                $params['kode_kec'] = explode(',', $params['kode_kec']);
            }
        } elseif ($user->can('staffProv')) {
            if (isset($params['kode_kec'])) {
                $params['kode_kec'] = explode(',', $params['kode_kec']);
            }
        } else {
            return 'Fitur download data BNBA tidak tersedia untuk user ini';
        }

        // export bnba
        $id = Yii::$app->queue->ttr(30 * 60)->push(new ExportBnbaJob([
            'params' => $params,
            'user_id' => $user->id,
        ]));

        return [ 'job_id' => $id ];
    }
}
