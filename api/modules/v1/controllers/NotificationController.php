<?php

namespace app\modules\v1\controllers;

use app\components\ModelHelper;
use app\filters\auth\HttpBearerAuth;
use app\models\Notification;
use app\models\NotificationSearch;
use app\models\User;
use Yii;
use yii\base\Model;
use yii\filters\AccessControl;
use yii\filters\auth\CompositeAuth;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

/**
 * NotificationController implements the CRUD actions for Notification model.
 */
class NotificationController extends ActiveController
{
    public $modelClass = Notification::class;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class'       => CompositeAuth::className(),
            'authMethods' => [
                HttpBearerAuth::className(),
            ],
        ];

        // remove authentication filter
        $auth = $behaviors['authenticator'];
        unset($behaviors['authenticator']);

        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::className(),
            'cors'  => [
                'Origin'                         => ['*'],
                'Access-Control-Request-Method'  => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
            ],
        ];

        // re-add authentication filter
        $behaviors['authenticator'] = $auth;
        // avoid authentication on CORS-pre-flight requests (HTTP OPTIONS method)
        $behaviors['authenticator']['except'] = ['options', 'public'];

        // setup access
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only'  => ['index', 'view', 'create', 'update', 'delete'], //only be applied to
            'rules' => [
                [
                    'allow'   => true,
                    'actions' => ['index', 'view', 'create', 'update', 'delete'],
                    'roles'   => ['admin', 'notificationManage'],
                ],
                [
                    'allow'   => true,
                    'actions' => ['index'],
                    'roles'   => ['user', 'staffRW'],
                ],
            ],
        ];

        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();

        // Override Delete Action
        unset($actions['delete']);
        unset($actions['create']);

        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];
        $actions['view']['findModel'] = [$this, 'findModel'];

        return $actions;
    }

    public function actionCreate()
    {
        /* @var $model \yii\db\ActiveRecord */
        $model = new $this->modelClass([
            'scenario' => Model::SCENARIO_DEFAULT,
        ]);

        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->validate() && $model->save()) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(201);
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        } else {
            // Validation error
            $response = \Yii::$app->getResponse();
            $response->setStatusCode(422);

            return $model->getErrors();
        }

        return $model;
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
        $model = $this->findModel($id, $this->modelClass);

        $this->checkAccess('delete', $model, $id);

        return $this->applySoftDelete($model);
    }

    /**
     * Checks the privilege of the current user.
     *
     * This method should be overridden to check whether the current user has the privilege
     * to run the specified action against the specified data model.
     * If the user does not have access, a [[ForbiddenHttpException]] should be thrown.
     *
     * @param string $action the ID of the action to be executed
     * @param object $model the model to be accessed. If null, it means no specific model is being accessed.
     * @param array $params additional parameters
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        // throw new ForbiddenHttpException();
    }

    /**
     * @param string $id
     * @param $model
     * @return mixed|Notification
     * @throws \yii\web\NotFoundHttpException
     */
    public function findModel(string $id, $model)
    {
        $status = [Notification::STATUS_PUBLISHED];
        $user = User::findIdentity(Yii::$app->user->getId());
        // Admin dan staf dapat mencari notification yang mempunyai status sebagai draft
        if ($user->role > User::ROLE_STAFF_RW) {
            \array_push($status, Notification::STATUS_DRAFT);
        }

        $searchedModel = Notification::find()
            ->where(['id' => $id])
            ->andWhere(['in', 'status',  $status]);

        if ($user->role < User::ROLE_ADMIN) {
            // staff dan user hanya boleh melihat Notification yang sesuai dengan area mereka
            $params = [
                'kabkota_id' => $user->kabkota_id,
                'kec_id' => $user->kec_id,
                'kel_id' => $user->kel_id,
                'rw' => $user->rw,
            ];
            $params = array_filter($params);
            $searchedModel = ModelHelper::filterByArea($searchedModel, $params);
        }

        $searchedModel = $searchedModel->one();

        if ($searchedModel === null) {
            throw new NotFoundHttpException("Object not found: $id");
        }

        return $searchedModel;
    }

    public function prepareDataProvider()
    {
        $search = new NotificationSearch();

        $user = User::findIdentity(Yii::$app->user->getId());

        return $search->search($user, Yii::$app->request->getQueryParams());
    }
}
