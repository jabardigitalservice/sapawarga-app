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
use app\modules\v1\repositories\QuestionRepository;
use app\modules\v1\repositories\LikeRepository;

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
                    'roles' => ['admin', 'pimpinan', 'staffProv', 'staffRW'],
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
        $repository = new QuestionRepository();
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
     * Delete entity with soft delete / status flagging
     *
     * @param $id
     */
    public function actionLikes($id)
    {
        $repository = new LikeRepository();
        $setLikeUnlike = $repository->setLikeUnlike($id, Like::TYPE_QUESTION);

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

        $search = new QuestionSearch();

        return $search->search($params);
    }
}
