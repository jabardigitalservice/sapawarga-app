<?php

namespace app\modules\v1\controllers;

use app\models\User;
use app\models\Gamification;
use app\models\GamificationSearch;
use app\models\GamificationParticipant;
use app\models\GamificationParticipantSearch;
use app\models\GamificationActivitySearch;
use app\models\GamificationMyBadgeSearch;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;

/**
 * GamificationController implements the CRUD actions for Gamification model.
 */
class GamificationController extends ActiveController
{
    public $modelClass = Gamification::class;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        return $this->behaviorCors($behaviors);
    }

    protected function behaviorAccess($behaviors)
    {
        // setup access
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => ['index', 'view', 'create', 'update', 'delete', 'join', 'my-task', 'participant', 'my-badge'],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['index', 'view', 'create', 'update', 'delete', 'participant'],
                    'roles' => ['admin', 'staffProv'],
                ],
                [
                    'allow' => true,
                    'actions' => ['index', 'view', 'join', 'my-task', 'participant', 'my-badge'],
                    'roles' => ['staffRW'],
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
     * @return mixed|Gamification
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionView($id)
    {
        $model = $this->findModel($id, $this->modelClass);
        return $model;
    }

    /**
     * Create new Gamification content
     *
     * @return Gamification
     * @throws HttpException
     * @throws InvalidConfigException
     */
    public function actionCreate()
    {
        $model = new Gamification();
        $model->scenario = Gamification::SCENARIO_CREATE;
        $model->load(\Yii::$app->getRequest()->getBodyParams(), '');

        if ($model->validate() && $model->save()) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(201);
        } else {
            $response = \Yii::$app->getResponse();
            $response->setStatusCode(422);

            return $model->getErrors();
        }

        return $model;
    }

    /**
     * Update Gamification content
     *
     * @return Gamification
     * @throws HttpException
     * @throws InvalidConfigException
     */
    public function actionUpdate($id)
    {
        $model = Gamification::findOne($id);

        if (empty($model)) {
            throw new NotFoundHttpException("Object not found: $id");
        }

        $model->scenario = Gamification::SCENARIO_UPDATE;
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');

        if ($model->validate() && $model->save()) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(200);
        } else {
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
     * User can join to the gamification
     *
     * @param $id id of gamification
     * @return mixed|Gamification
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionJoin($id)
    {
        $today = date('Y-m-d');

        // Check active gamification
        $isExistGamification = Gamification::find()
                ->where(['id' => $id])
                ->andWhere(['status' => Gamification::STATUS_ACTIVE])
                ->andWhere(['and', ['<=','start_date', $today],['>=','end_date', $today]])
                ->exists();

        if (! $isExistGamification) {
            throw new NotFoundHttpException("Object not found: $id");
        }

        // User join gamification
        $authUser = Yii::$app->user;
        $authUserId = $authUser->id;

        $isExistParticipant = GamificationParticipant::find()
                        ->where(['gamification_id' => $id, 'user_id' => $authUserId])
                        ->exists();

        $model = null;
        if (! $isExistParticipant) {
            $model = new GamificationParticipant();
            $model->gamification_id = $id;
            $model->user_id = $authUserId;
            $model->save(false);
        }

        return $model;
    }

    /**
     * List of gamification participant
     *
     * @param $id id of gamification
     * @return mixed|Gamification
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionParticipant()
    {
        $params = Yii::$app->request->getQueryParams();
        $authUser = Yii::$app->user;

        $params['user_id'] = $authUser->id;

        $search = new GamificationParticipantSearch();

        if ($authUser->can('user') || $authUser->can('staffRW')) {
            $search->scenario = GamificationSearch::SCENARIO_LIST_USER;
        }

        return $search->search($params);
    }

    /**
     * Detail user task of every mission
     *
     * @param $id id of gamification
     * @return mixed|Gamification
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionMyTask($id)
    {
        $params = Yii::$app->request->getQueryParams();
        $authUser = Yii::$app->user;
        $authUserId = $authUser->id;

        $params['user_id'] = $authUserId;

        $search = new GamificationActivitySearch();
        return $search->search($params);
    }

    /**
     * List of User badges
     *
     * @param $id id of gamification
     * @return mixed|Gamification
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionMyBadge()
    {
        $params = Yii::$app->request->getQueryParams();
        $authUser = Yii::$app->user;
        $authUserId = $authUser->id;

        $params['user_id'] = $authUserId;

        $search = new GamificationMyBadgeSearch();
        return $search->search($params);
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
        $authUser = Yii::$app->user;

        $search = new GamificationSearch();

        if ($authUser->can('user') || $authUser->can('staffRW')) {
            $params['user_id'] = $authUser->id;
            $search->scenario = GamificationSearch::SCENARIO_LIST_USER;
        }

        return $search->search($params);
    }
}
