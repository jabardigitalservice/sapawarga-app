<?php

namespace app\modules\v1\controllers;

use app\models\Question;
use app\models\QuestionSearch;
use app\models\Like;
use yii\filters\AccessControl;
use Illuminate\Support\Arr;
use Yii;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;

class QuestionController extends ActiveController
{
    public $modelClass = Question::class;

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
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => ['index', 'view', 'create', 'update', 'delete'],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['index', 'view', 'create', 'update', 'delete'],
                    // 'roles' => ['questionManage'],
                    'roles' => ['@'],
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
        unset($actions['delete']);

        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

        return $actions;
    }

     /**
     * @param $id
     * @return mixed|Question
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionView($id)
    {
        $model = $this->findModel($id, $this->modelClass);
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
     * Delete entity with soft delete / status flagging
     *
     * @param $id
     */
    public function actionLikes($id)
    {
        $userId = Yii::$app->user->getId();
        $userLike = Like::find()->where(['entity_id' => $id])
            ->andWhere(['type' => Like::TYPE_QUESTION])
            ->andWhere(['user_id' => $userId])
            ->one();

        if (! empty($userLike)) {
            $unlike = Like::findOne($userLike->id);
            $unlike->delete();
        } else {
            $like = new Like();
            $like->entity_id = $id;
            $like->user_id = $userId;
            $like->type = Like::TYPE_QUESTION;
            $like->save();
        }

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
        $authUser = Yii::$app->user;
        $authUserId = $authUser->id;

        // Admin can do everything
        if ($authUser->can('admin')) {
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

        $search = new QuestionSearch();

        return $search->search($params);
    }
}
