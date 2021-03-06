<?php

namespace app\modules\v1\controllers;

use app\models\Like;
use app\models\NewsImportant;
use app\models\NewsImportantSearch;
use app\models\NewsImportantAttachment;
use app\modules\v1\repositories\LikeRepository;
use Yii;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use app\components\GamificationActivityHelper;

/**
 * NewsImportantController implements the CRUD actions for NewsImportant model.
 */
class NewsImportantController extends ActiveController
{
    public $modelClass = NewsImportant::class;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        array_push($behaviors['verbs']['actions'], ['likes' => ['post']]);

        return $this->behaviorCors($behaviors);
    }

    protected function behaviorAccess($behaviors)
    {
        // add optional authentication for public endpoints
        $behaviors['authenticator']['optional'] = ['view'];

        // setup access
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => ['index', 'view', 'create', 'update', 'delete', 'likes'],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['index', 'view', 'create', 'update', 'delete'],
                    'roles' => ['newsImportantManage'],
                ],
                [
                    'allow' => true,
                    'actions' => ['index', 'view', 'likes'],
                    'roles' => ['newsImportantList'],
                ],
                [
                    'allow' => true,
                    'actions' => ['view'],
                    'roles' => ['?'],
                ],
            ],
        ];

        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();

        // Override Actions
        unset($actions['view']);
        unset($actions['create']);
        unset($actions['update']);
        unset($actions['delete']);

        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

        return $actions;
    }

    /**
     * @param $id
     * @return mixed|NewsImportant
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionView($id)
    {
        $query = NewsImportant::find()->where(['id' => $id]);
        $user = Yii::$app->user;

        $statuses = [NewsImportant::STATUS_PUBLISHED];
        if ($user->can('newsImportantManage')) {
            array_push($statuses, NewsImportant::STATUS_DISABLED);
        }

        $query->andWhere(['in', 'status',  $statuses]);

        $searchedModel = $query->one();
        if ($searchedModel === null) {
            throw new NotFoundHttpException("Object not found: $id");
        }

        $this->incrementTotalViewers($searchedModel);

        // Record gamification
        GamificationActivityHelper::saveGamificationActivity('news_important_view_detail', $id);

        return $searchedModel;
    }

    /**
     * Create new news important content
     *
     * @return NewsImportant
     * @throws HttpException
     * @throws InvalidConfigException
     */
    public function actionCreate()
    {
        $model = new NewsImportant();
        $model->load(\Yii::$app->getRequest()->getBodyParams(), '');

        if ($model->validate() && $model->save()) {
            $this->prepareSaveAttachment($model->id);

            $response = Yii::$app->getResponse();
            $response->setStatusCode(201);
        } else {
            // Validation error
            $response = \Yii::$app->getResponse();
            $response->setStatusCode(422);

            return $model->getErrors();
        }

        return $model;
    }

    /**
     * Update news important content
     *
     * @return News Important
     * @throws HttpException
     * @throws InvalidConfigException
     */
    public function actionUpdate($id)
    {
        $model = NewsImportant::findOne($id);
        $params = Yii::$app->getRequest()->getBodyParams();

        if (empty($model)) {
            throw new NotFoundHttpException("Object not found: $id");
        }

        $this->checkAccess('update', $model, $id);

        $model->load($params, '');

        if ($model->validate() && $model->save()) {
            // Delete first when send attachment
            if (isset($params['attachments'])) {
                $this->prepareDeleteAttachment($model->id);
                $this->prepareSaveAttachment($model->id);
            }

            $response = Yii::$app->getResponse();
            $response->setStatusCode(200);
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
     * Gives like/unlike to an entity
     *
     * @param $id
     */
    public function actionLikes($id)
    {
        $repository = new LikeRepository();
        $setLikeUnlike = $repository->setLikeUnlike($id, Like::TYPE_NEWS_IMPORTANT);
        $likesCount = $repository->getLikesCount($id, Like::TYPE_NEWS_IMPORTANT);

        // Update likes_count
        $updateLikesCount = NewsImportant::findOne($id);
        $updateLikesCount->likes_count = $likesCount;
        $updateLikesCount->save();

        $response = Yii::$app->getResponse();
        $response->setStatusCode(200);

        return 'ok';
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

        $search = new NewsImportantSearch();

        $search->scenario = $user->can('newsImportantManage') === true ?
            NewsImportantSearch::SCENARIO_LIST_STAFF :
            NewsImportantSearch::SCENARIO_LIST_USER;

        return $search->search($params);
    }

    /**
     * @param $newsImportantId
     * @return mixed|\app\models\NewsImportantAttachment
     */
    private function prepareSaveAttachment($newsImportantId)
    {
        $params = Yii::$app->getRequest()->getBodyParams();
        if (!empty($params['attachments'])) {
            foreach ($params['attachments'] as $val) {
                $this->saveAttachment($newsImportantId, $val);
            }
        }
    }

    /**
     * @param $newsImportantId id of news important
     * @param $val file path of atatchment
     * @return mixed|\app\models\NewsImportantAttachment
     */
    private function saveAttachment($newsImportantId, $val)
    {
        if (!empty($val['file_path'])) {
            $model = new NewsImportantAttachment();
            $model->news_important_id = $newsImportantId;
            $model->file_path = $val['file_path'];
            $model->save(false);
        }
    }

    /**
     * @param $newsImportantId id of news important
     * @return mixed|\app\models\NewsImportant
     */
    private function prepareDeleteAttachment($newsImportantId)
    {
        $newsImportant = NewsImportantAttachment::find()
            ->where(['news_important_id' => $newsImportantId])
            ->all();

        if (!empty($newsImportant)) {
            foreach ($newsImportant as $val) {
                $this->deleteAttachment($val->id, $val->file_path);
            }
        }
    }

    /**
     * @param $id id of atatchment
     * @param $filePath file path of atatchment
     * @return mixed|\app\models\NewsImportant
     */
    private function deleteAttachment($id, $filePath)
    {
        $model = NewsImportantAttachment::findOne($id);
        $model->delete();

        // To Do : Need delete file or not?
        // $delete = Yii::$app->fs->delete($filePath);

        return $model;
    }

    /**
     * Increments total viewers of a NewsImportant model
     * @param NewsImportant $model
     */
    private function incrementTotalViewers($model)
    {
        // Increment total views for roles other than 'newsImportantManage'
        if (Yii::$app->user->can('newsImportantList') === true) {
            $model->total_viewers = $model->total_viewers + 1;
            $model->save(false);
        }
    }
}
