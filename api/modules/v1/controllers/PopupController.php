<?php

namespace app\modules\v1\controllers;

use app\filters\auth\HttpBearerAuth;
use app\models\User;
use app\models\Popup;
use app\models\PopupSearch;
use Illuminate\Support\Arr;
use Yii;
use yii\filters\AccessControl;
use yii\filters\auth\CompositeAuth;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\ForbiddenHttpException;

/**
 * PopupController implements the CRUD actions for Popup model.
 */
class PopupController extends ActiveController
{
    public $modelClass = Popup::class;

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
                    'roles' => ['popupManage'],
                ],
                [
                    'allow' => true,
                    'actions' => ['index', 'view'],
                    'roles' => ['popupList'],
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
        unset($actions['update']);

        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];
        $actions['view']['findModel'] = [$this, 'findModel'];

        return $actions;
    }

    /**
     * Create new popup content
     *
     * @return Popup
     * @throws HttpException
     * @throws InvalidConfigException
     */
    public function actionCreate()
    {
        $model = new Popup();
        $model->scenario = Popup::SCENARIO_CREATE;
        $model->load(\Yii::$app->getRequest()->getBodyParams(), '');

        if ($model->validate() && $model->save()) {
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
     * Update popup content
     *
     * @return Popup
     * @throws HttpException
     * @throws InvalidConfigException
     */
    public function actionUpdate($id)
    {
        $model = Popup::findOne($id);

        if (empty($model)) {
            throw new NotFoundHttpException("Object not found: $id");
        }

        $params = Yii::$app->request->getQueryParams();
        $model->scenario = Popup::SCENARIO_UPDATE;
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');

        if ($model->validate() && $model->save()) {
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
     * @return mixed|\app\models\Popup
     * @throws \yii\web\NotFoundHttpException
     */
    public function findModel($id)
    {
        $model = Popup::find()
            ->where(['id' => $id])
            ->andWhere(['!=', 'status', Popup::STATUS_DELETED])
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

        $search = new PopupSearch();

        if ($user->can('popupManage') === true) {
            $search->scenario = PopupSearch::SCENARIO_LIST_STAFF;
        }

        return $search->search($params);
    }
}
