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
                [
                    'allow' => true,
                    'actions' => ['index'],
                    'roles' => ['service_account_dashboard'],
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
        $params = Yii::$app->getRequest()->getBodyParams();
        $model->load($params, '');

        $this->checkAccess('create', $model);

        // For old version still can upload one image
        if (!empty($params['image_path']) && empty($params['images'])) {
            $imagePath = [['path' => $params['image_path']]];
            $model->images = $imagePath;
        }

        // For new version can post and view from the old version
        // Get the first image on multiple image
        if (empty($params['image_path']) && !empty($params['images'])) {
            $images = $params['images'][0]['path'];
            $model->image_path = $images;
        }

        if ($model->validate() && $model->save()) {
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
        $setLikeAndCount = $this->setLikeAndCount($id, Like::TYPE_USER_POST, $this->modelClass);

        if ($setLikeAndCount) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(200);

            return 'ok';
        }
    }

    /**
     * Get list user post per user
     *
     * @param $id
     */
    public function actionMe()
    {
        $userId = Yii::$app->user->getId();

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
        return $this->checkAccessDefault($action, $model, $params);
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
