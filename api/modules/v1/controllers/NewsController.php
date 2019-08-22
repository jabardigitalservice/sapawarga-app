<?php

namespace app\modules\v1\controllers;

use app\filters\auth\HttpBearerAuth;
use app\models\User;
use app\models\News;
use app\models\NewsSearch;
use app\models\NewsStatistics;
use app\models\NewsViewer;
use Yii;
use yii\filters\AccessControl;
use yii\filters\auth\CompositeAuth;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

/**
 * NewsController implements the CRUD actions for News model.
 */
class NewsController extends ActiveController
{
    public $modelClass = News::class;

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
                'create' => ['post'],
                'update' => ['put'],
                'delete' => ['delete'],
                'public' => ['get'],
                'featured' => ['get'],
                'statistics' => ['get'],
            ],
        ];

        return $this->behaviorCors($behaviors);
    }

    protected function behaviorAccess($behaviors)
    {
        // setup access
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => ['index', 'view', 'create', 'update', 'delete', 'featured', 'statistics', 'related'],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['index', 'view', 'create', 'update', 'delete', 'featured', 'statistics'],
                    'roles' => ['newsManage'],
                ],
                [
                    'allow' => true,
                    'actions' => ['index', 'view', 'featured', 'related'],
                    'roles' => ['newsList'],
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

        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];
        $actions['view']['findModel']            = [$this, 'findModel'];

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
        $model = $this->findModel($id);

        $this->checkAccess('delete', $model, $id);

        $model->status = News::STATUS_DELETED;

        if ($model->save(false) === false) {
            throw new ServerErrorHttpException('Failed to delete the object for unknown reason.');
        }

        $response = Yii::$app->getResponse();
        $response->setStatusCode(204);

        return 'ok';
    }

    public function actionFeatured()
    {
        $params = Yii::$app->request->getQueryParams();

        $search = new NewsSearch();

        $search->scenario = NewsSearch::SCENARIO_LIST_USER;

        return $search->featuredList($params);
    }

    public function actionRelated()
    {
        $params = Yii::$app->request->getQueryParams();

        $search = new NewsSearch();

        $search->scenario = NewsSearch::SCENARIO_LIST_USER;

        return $search->relatedList($params);
    }

    public function actionStatistics()
    {
        $params = Yii::$app->request->getQueryParams();
        $statistics = new NewsStatistics();
        return $statistics->getStatistics($params);
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
        //
    }

    /**
     * @param $id
     * @return mixed|\app\models\News
     * @throws \yii\web\NotFoundHttpException
     */
    public function findModel($id)
    {
        $model = News::find()
            ->where(['id' => $id])
            ->andWhere(['!=', 'status', News::STATUS_DELETED])
            ->one();

        if ($model === null) {
            throw new NotFoundHttpException("Object not found: $id");
        }

        $userDetail = User::findIdentity(Yii::$app->user->getId());

        // Increment total views for specific role
        if ($userDetail->role === User::ROLE_USER || $userDetail->role === User::ROLE_STAFF_RW) {
            $totalViewers = $model->total_viewers + 1;

            $this->saveNewsViewerPerUser($id);

            $model->total_viewers = $totalViewers;
            $model->save(false);
        }

        return $model;
    }

    private function saveNewsViewerPerUser($newsId)
    {
        $userId = Yii::$app->user->id;

        $newsViewer = NewsViewer::find()
                ->where(['news_id' => $newsId, 'user_id' => $userId ])
                ->one();

        // New when not exist, update if exist
        if ($newsViewer === null) {
            $model = new NewsViewer();

            $model->news_id = $newsId;
            $model->user_id = $userId;
            $model->read_count = 1;
            $model->save(false);
        } else {
            $newsViewer->news_id = $newsId;
            $newsViewer->user_id = $userId;
            $newsViewer->read_count = $newsViewer->read_count + 1;
            $newsViewer->save(false);
        }
    }


    public function prepareDataProvider()
    {
        $params = Yii::$app->request->getQueryParams();

        $user   = Yii::$app->user;
        $authUserModel = $user->identity;
        $authKabKotaId = $authUserModel->kabkota_id;

        $search = new NewsSearch();
        $search->userRole = $authUserModel->role;

        if ($user->can('newsManage') === false) {
            $search->scenario = NewsSearch::SCENARIO_LIST_USER;
        }

        if ($user->can('staffKabkota')) {
            $params['kabkota_id'] = $authKabKotaId;
        }

        return $search->search($params);
    }
}
