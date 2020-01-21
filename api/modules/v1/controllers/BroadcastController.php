<?php

namespace app\modules\v1\controllers;

use app\components\ModelHelper;
use app\models\Broadcast;
use app\models\BroadcastSearch;
use app\models\User;
use app\models\UserMessage;
use Yii;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

/**
 * BroadcastController implements the CRUD actions for Broadcast model.
 */
class BroadcastController extends ActiveController
{
    public $modelClass = Broadcast::class;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

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

        unset($actions['create']);
        unset($actions['view']);
        unset($actions['update']);
        unset($actions['delete']);

        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

        return $actions;
    }

    /**
     * Create and send Broadcast message (or just set scheduled status for a scheduled message type)
     *
     * @return array|string
     * @throws \yii\web\ServerErrorHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionCreate()
    {
        $response = Yii::$app->getResponse();

        /* @var $model \yii\db\ActiveRecord|Broadcast */
        $model = new $this->modelClass();

        // create model and populate property values
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        $model->author_id = Yii::$app->user->getId();

        // validating broadcast message
        if ($model->validate() === false) {
            return $this->buildValidationFailedResponse($model);
        }

        // save broadcast message to database
        if ($model->save() === false) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }

        // prepare  API response
        $response->setStatusCode(201);

        $this->sendOrScheduleMessage($model);

        return $model;
    }

    /**
     * @param $id
     * @return mixed|\app\models\Broadcast
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionView($id)
    {
        $model = $this->findModel($id, $this->modelClass);

        // Mark UserMessage as read
        $userMessageModel = UserMessage::find()
            ->where(['type' => Broadcast::CATEGORY_TYPE])
            ->andWhere(['message_id' => $id])
            ->andWhere(['recipient_id' => Yii::$app->user->getId()])
            ->one();
        if ($userMessageModel !== null) {
            if ($userMessageModel->read_at === null) {
                $userMessageModel->touch('read_at');
                $userMessageModel->save(false);
            }
        }

        $response = Yii::$app->getResponse();
        $response->setStatusCode(200);

        return $model;
    }

    /**
     * Update existing record and send Broadcast message (or just set scheduled status for a scheduled message type)
     *
     * @param $id
     * @return \app\models\Broadcast|array
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\web\NotFoundHttpException
     * @throws \yii\web\ServerErrorHttpException
     */
    public function actionUpdate($id)
    {
        $model = Broadcast::findOne($id);

        if ($model === null) {
            throw new NotFoundHttpException("Object not found: $id");
        }

        $model->load(Yii::$app->getRequest()->getBodyParams(), '');

        if ($model->validate() === false) {
            return $this->buildValidationFailedResponse($model);
        }

        if ($model->save() === false) {
            throw new ServerErrorHttpException('Failed to update the object for unknown reason.');
        }

        $this->sendOrScheduleMessage($model);

        return $model;
    }

    /**
     * Build validation error response
     *
     * @param \app\models\Broadcast $model
     * @return array
     */
    protected function buildValidationFailedResponse(Broadcast $model): array
    {
        $response = Yii::$app->getResponse();
        $response->setStatusCode(422);

        return $model->getErrors();
    }

    /**
     * Update Broadcast status depends on schedule input, or send broadcast if not scheduled
     *
     * @param \app\models\Broadcast $model
     * @return \app\models\Broadcast
     */
    protected function sendOrScheduleMessage(Broadcast $model)
    {
        // if created as draft, do nothing
        if ($model->isDraft()) {
            return $model;
        }

        // if record has scheduled attribute, change status to scheduled
        // if not a scheduled message, create queue for send broadcast message now

        if ($model->isScheduled()) {
            $model->status = Broadcast::STATUS_SCHEDULED;
            $model->save();
        } else {
            Broadcast::pushSendMessageToUserJob($model);
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
     * @param string $id
     * @param $model
     * @return mixed|Broadcast
     * @throws \yii\web\NotFoundHttpException
     */
    public function findModel(string $id, $model)
    {
        $status = [
            Broadcast::STATUS_DRAFT,
            Broadcast::STATUS_SCHEDULED,
            Broadcast::STATUS_PUBLISHED,
        ];

        $user = User::findIdentity(Yii::$app->user->getId());

        $searchedModel = Broadcast::find()
            ->where(['id' => $id])
            ->andWhere(['in', 'status',  $status]);

        if ($user->role < User::ROLE_ADMIN) {
            // staff dan user hanya boleh melihat broadcast yang sesuai dengan area mereka
            if ($user->kabkota_id) {
                $searchedModel->andWhere(['or',
                ['kabkota_id' => $user->kabkota_id],
                ['kabkota_id' => null]]);
            }
            if ($user->kec_id) {
                $searchedModel->andWhere(['or',
                ['kec_id' => $user->kec_id],
                ['kec_id' => null]]);
            }
            if ($user->kel_id) {
                $searchedModel->andWhere(['or',
                ['kel_id' => $user->kel_id],
                ['kel_id' => null]]);
            }
            if ($user->rw) {
                $searchedModel->andWhere(['or',
                ['rw' => $user->rw],
                ['rw' => null]]);
            }
        }

        $searchedModel = $searchedModel->one();

        if ($searchedModel === null) {
            throw new NotFoundHttpException("Object not found: $id");
        }

        return $searchedModel;
    }

    public function prepareDataProvider()
    {
        return $this->dataProviderStaff();
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
        $search->user_id  = ModelHelper::getLoggedInUserId();

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
