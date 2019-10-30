<?php

namespace app\modules\v1\controllers;

use app\filters\auth\HttpBearerAuth;
use app\models\User;
use app\models\UserMessage;
use app\models\UserMessageSearch;
use Hashids\Hashids;
use Illuminate\Support\Arr;
use Jdsteam\Sapawarga\Filters\RecordLastActivity;
use Yii;
use yii\filters\AccessControl;
use yii\filters\auth\CompositeAuth;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;

/**
 * MessageController implements the CRUD actions for User Message model.
 */
class UserMessageController extends ActiveController
{
    public $modelClass = UserMessage::class;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            'authMethods' => [
                HttpBearerAuth::className(),
            ],
        ];

        $behaviors['verbs'] = [
            'class' => VerbFilter::className(),
            'actions' => [
                'index' => ['get'],
                'view' => ['get'],
                'delete' => ['delete'],
                'bulk-delete' => ['post'],
            ],
        ];

        $behaviors['recordLastActivity'] = [
            'class' => RecordLastActivity::class,
        ];

        return $this->behaviorCors($behaviors);
    }

    protected function behaviorCors($behaviors)
    {
        // remove authentication filter
        $auth = $behaviors['authenticator'];
        unset($behaviors['authenticator']);

        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::className(),
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'DELETE'],
                'Access-Control-Request-Headers' => ['*'],
            ],
        ];

        // re-add authentication filter
        $behaviors['authenticator'] = $auth;
        // avoid authentication on CORS-pre-flight requests (HTTP OPTIONS method)
        $behaviors['authenticator']['except'] = ['options', 'public'];

        return $this->behaviorAccess($behaviors);
    }

    protected function behaviorAccess($behaviors)
    {
        // setup access
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => ['index', 'view', 'delete', 'bulk-delete'], //only be applied to
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['index', 'view', 'delete', 'bulk-delete'],
                    'roles' => ['userMessageList'],
                ],
            ],
        ];

        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();

        unset($actions['view']);
        unset($actions['delete']);

        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

        return $actions;
    }

    /**
     * @param $id
     * @return mixed|\app\models\UserMessage
     * @throws \yii\web\NotFoundHttpException
     */
    public function findModel($id)
    {
        $idDecode = $this->decodeHashIds([$id]);

        if (empty($idDecode[0])) {
            throw new NotFoundHttpException("Object not found: $id");
        }

        $userDetail = User::findIdentity(Yii::$app->user->getId());

        $model = UserMessage::find()
            ->where(['id' => $idDecode[0]])
            ->andWhere(['<>', 'status', UserMessage::STATUS_DELETED])
            ->andWhere(['=', 'recipient_id', $userDetail->id])
            ->one();

        if ($model === null) {
            throw new NotFoundHttpException("Object not found: $id");
        }

        return $model;
    }

    public function prepareDataProvider()
    {
        $params = Yii::$app->request->getQueryParams();

        $authUser = Yii::$app->user;
        $authUserModel = $authUser->identity;
        $params['user_id'] = $authUserModel->id;

        $search = new UserMessageSearch();

        return $search->search($params);
    }

    /**
     * @param $id
     * @return mixed|\app\models\UserMessage
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);

        // Mark UserMessage as read
        $model->touch('read_at');
        $model->save(false);

        $response = Yii::$app->getResponse();
        $response->setStatusCode(200);

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

        return $this->applySoftDelete($model);
    }

    public function actionBulkDelete()
    {
        $request = Yii::$app->getRequest()->getBodyParams();
        $deletedIds = $this->decodeHashIds(Arr::get($request, 'ids'));

        // bulk soft-delete
        UserMessage::updateAll(
            ['status' => UserMessage::STATUS_DELETED],
            ['in', 'id', $deletedIds]
        );

        $response = Yii::$app->getResponse();
        $response->setStatusCode(204);

        return 'ok';
    }

    protected function decodeHashIds($hashIds)
    {
        $decoder = new Hashids(\Yii::$app->params['hashidSaltSecret'], \Yii::$app->params['hashidLengthPad']);
        $decodedIds = [];
        foreach ($hashIds as $hashId) {
            $id = $decoder->decode($hashId);
            // handles invalid hashId
            if (empty($id)) {
                $id = [''];
            }
            array_push($decodedIds, $id[0]);
        }
        return $decodedIds;
    }
}
