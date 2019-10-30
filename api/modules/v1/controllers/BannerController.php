<?php

namespace app\modules\v1\controllers;

use app\filters\auth\HttpBearerAuth;
use app\models\User;
use app\models\Banner;
use app\models\BannerSearch;
use Illuminate\Support\Arr;
use Yii;
use yii\filters\AccessControl;
use yii\filters\auth\CompositeAuth;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\ForbiddenHttpException;

/**
 * BannerController implements the CRUD actions for Banner model.
 */
class BannerController extends ActiveController
{
    public $modelClass = Banner::class;

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
            ],
        ];

        return $this->behaviorCors($behaviors);
    }

    protected function behaviorAccess($behaviors)
    {
        // setup access
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => ['index', 'view', 'create', 'update', 'delete'],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['index', 'view', 'create', 'update', 'delete'],
                    'roles' => ['bannerManage'],
                ],
                [
                    'allow' => true,
                    'actions' => ['index', 'view'],
                    'roles' => ['bannerList'],
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
     * @return mixed|\app\models\Banner
     * @throws \yii\web\NotFoundHttpException
     */
    public function findModel($id)
    {
        $model = Banner::find()
            ->where(['id' => $id])
            ->andWhere(['!=', 'status', Banner::STATUS_DELETED])
            ->one();

        if ($model === null) {
            throw new NotFoundHttpException("Object not found: $id");
        }

        return $model;
    }

    public function prepareDataProvider()
    {
        $params = Yii::$app->request->getQueryParams();

        $user = Yii::$app->user;

        $search = new BannerSearch();

        if ($user->can('bannerManage') === true) {
            $search->scenario = BannerSearch::SCENARIO_LIST_STAFF;
        }

        return $search->search($params);
    }
}
