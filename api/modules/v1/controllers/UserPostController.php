<?php

namespace app\modules\v1\controllers;

use app\components\LogHelper;
use app\models\UserPost;
use app\models\UserPostSearch;
use app\models\User;
use app\models\Like;
use yii\filters\AccessControl;
use Illuminate\Support\Arr;
use Yii;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use app\modules\v1\repositories\UserPostRepository;
use app\modules\v1\repositories\LikeRepository;
use app\components\GamificationActivityHelper;

class UserPostController extends ActiveController
{
    public $modelClass = UserPost::class;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['verbs'] = [
            'class' => VerbFilter::className(),
            'actions' => [
                'index' => ['get'],
                'view' => ['get'],
                'create' => ['post'],
                'update' => ['put'],
                'delete' => ['delete'],
                'me' => ['get'],
            ],
        ];

        return $this->behaviorCors($behaviors);
    }

    protected function behaviorAccess($behaviors)
    {
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => ['index', 'view', 'create', 'update', 'delete', 'me'],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['index', 'view', 'create', 'update', 'delete', 'me'],
                    'roles' => ['admin', 'staffProv', 'staffRW', 'pimpinan'],
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
        unset($actions['delete']);

        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

        return $actions;
    }

    /**
     * @return UserPost|array
     * @throws ServerErrorHttpException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionCreate()
    {
        $model = new UserPost();
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');

        $this->checkAccess('create', $model);

        if ($model->validate() && $model->save()) {
            // Record gamification
            GamificationActivityHelper::saveGamificationActivity('user_post_create', $model->id);

            LogHelper::logEventByUser('USER_POST_CREATE');

            $response = Yii::$app->getResponse();
            $response->setStatusCode(201);
        } else {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(422);

            return $model->getErrors();
        }

        return $model;
    }

     /**
     * @param $id
     * @return mixed|UserPost
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionView($id)
    {
        $repository = new UserPostRepository();
        $getDetail = $repository->getDetail($id);

        if ($getDetail === null) {
            throw new NotFoundHttpException("Object not found: $id");
        }

        return $getDetail;
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
     * Like or Unlike user post
     *
     * @param $id
     */
    public function actionLikes($id)
    {
        $repository = new LikeRepository();
        $setLikeUnlike = $repository->setLikeUnlike($id, Like::TYPE_USER_POST);
        $likesCount = $repository->getLikesCount($id, Like::TYPE_USER_POST);

        // Update likes_count
        $updateLikesCount = UserPost::findOne($id);
        $updateLikesCount->likes_count = $likesCount;
        $updateLikesCount->save();

        $response = Yii::$app->getResponse();
        $response->setStatusCode(200);

        return 'ok';
    }

    /**
     * Get list user post per user
     *
     * @param $id
     */
    public function actionMe()
    {
        $userId = Yii::$app->user->getId();
        $user = User::findIdentity($userId);

        $search = new UserPostSearch();
        $search->scenario = UserPostSearch::SCENARIO_LIST_USER;
        $search->created_by = $userId;

        $params = Yii::$app->request->getQueryParams();
        return $search->search($params, true);
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
        $authUser = Yii::$app->user;
        $authUserId = $authUser->id;

        // Admin, staffprov can do everything
        if ($authUser->can('admin') || $authUser->can('staffProv')) {
            return true;
        }

        if ($action === 'update' || $action === 'delete') {
            if ($model->created_by !== \Yii::$app->user->id) {
                throw new ForbiddenHttpException(Yii::t('app', 'error.role.permission'));
            }
        }
    }

    public function prepareDataProvider()
    {
        $params = Yii::$app->request->getQueryParams();

        $user = Yii::$app->user;
        $search = new UserPostSearch();
        if ($user->can('staffRW')) {
            $search->scenario = UserPostSearch::SCENARIO_LIST_USER;
        }

        return $search->search($params);
    }
}
