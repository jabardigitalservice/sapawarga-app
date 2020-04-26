<?php

namespace app\modules\v1\controllers;

use app\models\Area;
use app\models\Beneficiary;
use app\models\BeneficiarySearch;
use app\validator\NikValidator;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Yii;
use yii\base\DynamicModel;
use yii\filters\AccessControl;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;

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
            'only' => ['index', 'view', 'create', 'update', 'delete', 'nik', 'check-exist-nik'],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['index', 'view', 'create', 'update', 'delete', 'nik', 'check-exist-nik'],
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
        unset($actions['create']);
        unset($actions['view']);
        unset($actions['update']);
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
     * @return mixed|\app\models\Beneficiery
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionView($id)
    {
        $model = $this->findModel($id, $this->modelClass);
        return $model;
    }

    public function actionCreate()
    {
        $model = new Beneficiary();

        $model->load(Yii::$app->getRequest()->getBodyParams(), '');

        if ($model->validate() && $model->save()) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(201);
        } else {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(422);

            return $model->getErrors();
        }

        return $model;
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id, Beneficiary::class);
        $params = Yii::$app->getRequest()->getBodyParams();

        $model->load($params, '');

        if ($model->validate() && $model->save()) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(200);
        } else {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(422);

            return $model->getErrors();
        }

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
            // Get bps id
            $area = Area::find()->where(['id' => $authUserModel->kel_id])->one();

            $search->scenario = BeneficiarySearch::SCENARIO_LIST_USER;
            $params['domicile_kel_bps_id'] = $area->code_bps;
            $params['domicile_rw'] = $authUserModel->rw;
        }

        return $search->search($params);
    }

    /**
     * @param $id
     * @return mixed|\app\models\Beneficiery
     */
    public function actionCheckExistNik($id)
    {
        $model = Beneficiary::find()
            ->where(['nik' => $id])
            ->andWhere(['!=', 'status', Beneficiary::STATUS_DELETED])
            ->exists();

        $response = Yii::$app->getResponse();
        $response->setStatusCode(200);

        return $model;
    }

    /**
     * @param $nik
     * @throws \yii\web\HttpException
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionNik($nik)
    {
        $nikModel = new DynamicModel(['nik' => $nik]);
        $nikModel->addRule('nik', 'trim');
        $nikModel->addRule('nik', 'required');
        $nikModel->addRule('nik', NikValidator::class);

        if ($nikModel->validate() === false) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(422);

            return $nikModel->getErrors();
        }

        return 'OK';
    }
}
