<?php

namespace app\modules\v1\controllers;

use app\models\User;
use app\models\Video;
use app\models\VideoSearch;
use app\models\VideoStatistics;
use Jdsteam\Sapawarga\Filters\RecordLastActivity;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;

/**
 * VideoController implements the CRUD actions for Video model.
 */
class VideoController extends ActiveController
{
    public $modelClass = Video::class;

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
                'statistics' => ['get'],
                'likes' => ['post'],
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
            'only' => ['index', 'view', 'create', 'update', 'delete', 'statistics'], //only be applied to
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['index', 'view', 'create', 'update', 'delete',  'statistics'],
                    'roles' => ['videoManage'],
                ],
                [
                    'allow' => true,
                    'actions' => ['index', 'view', 'likes'],
                    'roles' => ['videoList'],
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
        $actions['view']['findModel'] = [$this, 'findModel'];

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

    public function actionStatistics()
    {
        $params = Yii::$app->request->getQueryParams();
        $statistics = new VideoStatistics();
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
     * @return mixed|\app\models\Video
     * @throws \yii\web\NotFoundHttpException
     */
    public function findModel($id)
    {
        $model = Video::find()
            ->where(['id' => $id])
            ->andWhere(['!=', 'status', Video::STATUS_DELETED])
            ->one();

        if ($model === null) {
            throw new NotFoundHttpException("Object not found: $id");
        }

        $userDetail = User::findIdentity(Yii::$app->user->getId());

        return $model;
    }

    public function prepareDataProvider()
    {
        $params = Yii::$app->request->getQueryParams();

        $authUser = Yii::$app->user;
        $authUserModel = $authUser->identity;

        $authKabKotaId = $authUserModel->kabkota_id;

        $search = new VideoSearch();

        if ($authUser->can('user') || $authUser->can('staffRW')) {
            $search->scenario = VideoSearch::SCENARIO_LIST_USER;
        }

        if ($authUser->can('staffKabkota')) {
            $params['kabkota_id'] = $authKabKotaId;
        }

        return $search->search($params);
    }
}
