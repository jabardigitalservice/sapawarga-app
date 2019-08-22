<?php

namespace app\modules\v1\controllers;

use app\filters\auth\HttpBearerAuth;
use app\models\Broadcast;
use app\models\BroadcastSearch;
use app\models\UserMessage;
use app\models\User;
use Illuminate\Support\Arr;
use Yii;
use yii\base\Model;
use yii\filters\AccessControl;
use yii\filters\auth\CompositeAuth;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use app\components\ModelHelper;

/**
 * BroadcastController implements the CRUD actions for Broadcast model.
 */
class BroadcastController extends ActiveController
{
    public $modelClass = Broadcast::class;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class'       => CompositeAuth::className(),
            'authMethods' => [
                HttpBearerAuth::className(),
            ],
        ];

        $behaviors['verbs'] = [
            'class'   => \yii\filters\VerbFilter::className(),
            'actions' => [
                'index'  => ['get'],
                'view'   => ['get'],
                'create' => ['post'],
                'update' => ['put'],
                'delete' => ['delete'],
                'public' => ['get'],
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
                    'roles'   => ['broadcastManage'],
                ],
                [
                    'allow'   => true,
                    'actions' => ['index', 'view'],
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
        /* @var $model \yii\db\ActiveRecord|Broadcast */
        $model = new $this->modelClass([
            'scenario' => Model::SCENARIO_DEFAULT,
        ]);

        $params = Yii::$app->request->getQueryParams();

        if (Arr::has($params, 'test')) {
            $model->setEnableSendPush(false);
        }

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
        $model = $this->findModel($id);

        $this->checkAccess('delete', $model, $id);

        $model->status = Broadcast::STATUS_DELETED;

        if ($model->save(false) === false) {
            throw new ServerErrorHttpException('Failed to delete the object for unknown reason.');
        }

        // Delete user_messages
        UserMessage::deleteAll(['message_id' => $id]);

        $response = Yii::$app->getResponse();
        $response->setStatusCode(204);

        return 'ok';
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
     * @param $id
     * @return mixed|Broadcast
     * @throws \yii\web\NotFoundHttpException
     */
    public function findModel($id)
    {
        $status = [Broadcast::STATUS_PUBLISHED];
        $user = User::findIdentity(Yii::$app->user->getId());
        // Admin dan staf dapat mencari broadcast yang mempunyai status sebagai draft
        if ($user->role > User::ROLE_STAFF_RW) {
            \array_push($status, Broadcast::STATUS_DRAFT);
        }

        $model = Broadcast::find()
            ->where(['id' => $id])
            ->andWhere(['in', 'status',  $status]);

        if ($user->role < User::ROLE_ADMIN) {
            // staff dan user hanya boleh melihat broadcast yang sesuai dengan area mereka
            if ($user->kabkota_id) {
                $model->andWhere(['or',
                ['kabkota_id' => $user->kabkota_id],
                ['kabkota_id' => null]]);
            }
            if ($user->kec_id) {
                $model->andWhere(['or',
                ['kec_id' => $user->kec_id],
                ['kec_id' => null]]);
            }
            if ($user->kel_id) {
                $model->andWhere(['or',
                ['kel_id' => $user->kel_id],
                ['kel_id' => null]]);
            }
            if ($user->rw) {
                $model->andWhere(['or',
                ['rw' => $user->rw],
                ['rw' => null]]);
            }
        }

        $model = $model->one();

        if ($model === null) {
            throw new NotFoundHttpException("Object not found: $id");
        }

        return $model;
    }

    public function prepareDataProvider()
    {
        $authUser = Yii::$app->user;

        if ($authUser->can('staffRW') || $authUser->can('user')) {
            return $this->dataProviderUser();
        }

        return $this->dataProviderStaff();
    }

    protected function dataProviderUser()
    {
        /**
         * @var \app\models\User $authUserModel
         */
        $authUser      = Yii::$app->user;
        $authUserModel = $authUser->identity;

        $authKabKotaId = $authUserModel->kabkota_id;
        $authKecId     = $authUserModel->kec_id;
        $authKelId     = $authUserModel->kel_id;
        $authRW        = $authUserModel->rw;

        $search           = new BroadcastSearch();
        $search->scenario = BroadcastSearch::SCENARIO_LIST_USER_DEFAULT;

        return $search->searchUser([
            'start_datetime' => $authUserModel->last_login_at,
            'kabkota_id'     => $authKabKotaId,
            'kec_id'         => $authKecId,
            'kel_id'         => $authKelId,
            'rw'             => $authRW,
        ]);
    }

    protected function dataProviderStaff()
    {
        $queryParams   = Yii::$app->request->getQueryParams();

        /**
         * @var \app\models\User $authUserModel
         */
        $authUser      = Yii::$app->user;
        $authUserModel = $authUser->identity;

        $authKabKotaId = $authUserModel->kabkota_id;
        $authKecId     = $authUserModel->kec_id;
        $authKelId     = $authUserModel->kel_id;

        $search           = new BroadcastSearch();
        $search->scenario = BroadcastSearch::SCENARIO_LIST_STAFF_DEFAULT;
        $search->user_id = ModelHelper::getLoggedInUserId();

        $params = $queryParams;

        if ($authUser->can('staffKabkota')) {
            $params['kabkota_id'] = $authKabKotaId;
        }

        if ($authUser->can('staffKec')) {
            $params['kabkota_id'] = $authKabKotaId;
            $params['kec_id']     = $authKecId;
        }

        if ($authUser->can('staffKel')) {
            $params['kabkota_id'] = $authKabKotaId;
            $params['kec_id']     = $authKecId;
            $params['kel_id']     = $authKelId;
        }

        return $search->searchStaff($params);
    }
}
