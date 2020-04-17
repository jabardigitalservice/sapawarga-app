<?php

namespace app\modules\v1\controllers;

use app\models\Beneficiary;
use app\models\BeneficiarySearch;
use GuzzleHttp\Client;
use Yii;
use yii\filters\AccessControl;

/**
 * BeneficiaryController implements the CRUD actions for Beneficiary model.
 */
class BeneficiariesController extends ActiveController
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
            'only' => ['index', 'view', 'create', 'update', 'delete', 'nik'],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['index', 'view', 'create', 'update', 'delete', 'nik'],
                    'roles' => ['admin', 'staffProv', 'staffKel', 'staffRW', 'trainer'],

                ]
            ],
        ];

        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();

        // Override Actions
        unset($actions['view']);
        unset($actions['delete']);

        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

        return $actions;
    }

    /**
     * Delete entity with soft delete / status flagging
     *
     * @param $id
     * @return string
     * @throws \yii\web\ForbiddenHttpException
     * @throws \yii\web\NotFoundHttpException
     * @throws \yii\web\ServerErrorHttpException
     */
    public function actionDelete($id)
    {
    }

    /**
     * Checks the privilege of the current user.
     * throw ForbiddenHttpException if access should be denied
     *
     * @param string $action the ID of the action to be executed
     * @param object $model the model to be accessed. If null, it means no specific model is being accessed.
     * @param array $params additional parameters
     */
    public function checkAccess($action, $model = null, $params = [])
    {
    }

    /**
     * @param $id
     * @return mixed|\app\models\News
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionView($id)
    {
        $model = $this->findModel($id, $this->modelClass);
        return $model;
    }

    public function prepareDataProvider()
    {
        $params = Yii::$app->request->getQueryParams();

        $user = Yii::$app->user;
        $authUserModel = $user->identity;

        $search = new BeneficiarySearch();
        $search->userRole = $authUserModel->role;

        if ($user->can('staffKel') || $user->can('staffRW') || $user->can('trainer')) {
            $search->scenario = BeneficiarySearch::SCENARIO_LIST_USER;
            $params['kel_id'] = $authUserModel->kel_id;
            $params['rw'] = $authUserModel->rw;
        }

        return $search->search($params);
    }


    /**
     * @param $id
     * @return array
     */
    public function actionNik($id)
    {
        $client = new Client([
            'base_uri' => getenv('KEPENDUDUKAN_API_BASE_URL')
        ]);
        $requestBody = [
            'json' => [
                'api_key' => getenv('KEPENDUDUKAN_API_KEY'),
                'event_key' => 'bansos/nik',
                'nik' => $id ,
            ],
        ];

        $response = $client->request('POST', 'kependudukan/nik', $requestBody);
        $responseBody = json_decode($response->getBody(), true);
        $model = $responseBody['data'];

        $model = [
            'nik' => strval($model['nik']),
            'no_kk' => strval($model['no_kk']),
            'name' => $model['nama'],
            'province_bps_id' => strval($model['no_prop']),
            'kabkota_bps_id' => $model['kode_kab_bps'],
            'kec_bps_id' => $model['kode_kec_bps'],
            'kel_bps_id' => $model['kode_kel_bps'],
            'province' => [
                'code_bps' => strval($model['no_prop']),
                'name' => '',
            ],
            'kabkota' => [
                'code_bps' => $model['kode_kab_bps'],
                'name' => $model['kab'],
            ],
            'kecamatan' => [
                'code_bps' => $model['kode_kec_bps'],
                'name' => $model['kec'],
            ],
            'kelurahan' => [
                'code_bps' => $model['kode_kel_bps'],
                'name' => $model['kel'],
            ],
            'rt' => strval($model['rt']),
            'rw' => strval($model['rw']),
            'address' => $model['alamat'],
        ];

        return $model;
    }
}
