<?php

namespace app\modules\v1\controllers;

use app\models\NewsFeatured;
use app\models\News;
use app\models\NewsSearch;
use app\models\NewsStatistics;
use app\models\NewsViewer;
use app\modules\v1\repositories\NewsFeaturedRepository;
use Illuminate\Support\Arr;
use Jdsteam\Sapawarga\Filters\RecordLastActivity;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;

/**
 * NewsController implements the CRUD actions for News model.
 */
class NewsController extends ActiveController
{
    public $modelClass = News::class;

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
                'public' => ['get'],
                'featured' => ['get', 'post'],
                'statistics' => ['get'],
            ],
        ];

        $behaviors['recordLastActivity'] = [
            'class' => RecordLastActivity::class,
        ];

        return $this->behaviorCors($behaviors);
    }

    protected function behaviorAccess($behaviors)
    {
        // setup access
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => ['index', 'view', 'create', 'update', 'delete', 'featured', 'featured-update', 'statistics', 'related'],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['index', 'view', 'create', 'update', 'delete', 'featured', 'featured-update', 'statistics'],
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

        return $this->applySoftDelete($model);
    }

    public function actionFeatured()
    {
        $params     = Yii::$app->request->getQueryParams();
        $repository = new NewsFeaturedRepository();

        return $repository->getList($params);
    }

    public function actionFeaturedUpdate()
    {
        $params    = Yii::$app->request->getQueryParams();
        $kabkotaId = Arr::get($params, 'kabkota_id');

        $records   = Yii::$app->getRequest()->getBodyParams();

        return $this->parseInputFeatured($kabkotaId, $records);
    }

    protected function parseInputFeatured($kabkotaId, $records)
    {
        $repository = new NewsFeaturedRepository();
        $repository->resetFeatured($kabkotaId);

        foreach ($records as $record) {
            $result = $this->saveFeatured($kabkotaId, $record);

            if ($result !== true) {
                return $result;
            }
        }

        $response = Yii::$app->getResponse();
        $response->setStatusCode(200);

        return $response;
    }

    protected function saveFeatured($kabkotaId, $record)
    {
        if ($kabkotaId !== null) {
            $record['kabkota_id'] = $kabkotaId;
        }

        $model = new NewsFeatured();
        $model->load($record, '');

        if ($model->validate() === false) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(422);

            return $model->getErrors();
        }

        if ($model->save() === false) {
            return $model->getErrors();
        }

        return true;
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
     * throw ForbiddenHttpException if access should be denied
     *
     * @param string $action the ID of the action to be executed
     * @param object $model the model to be accessed. If null, it means no specific model is being accessed.
     * @param array $params additional parameters
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        if ($action === 'update' || $action === 'delete') {
            if ($model->created_by !== \Yii::$app->user->id) {
                throw new ForbiddenHttpException(Yii::t('app', 'error.role.permission'));
            }
        }
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

        // Increment total views for specific role
        if (Yii::$app->user->can('newsList')) {
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
