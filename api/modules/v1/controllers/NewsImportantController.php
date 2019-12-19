<?php

namespace app\modules\v1\controllers;

use app\models\NewsImportant;
use app\models\NewsImportantSearch;
use app\models\NewsImportantAttachment;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;

/**
 * NewsImportantController implements the CRUD actions for NewsImportant model.
 */
class NewsImportantController extends ActiveController
{
    public $modelClass = NewsImportant::class;

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
                    'roles' => ['newsImportantManage'],
                ],
                [
                    'allow' => true,
                    'actions' => ['index', 'view'],
                    'roles' => ['newsImportantList'],
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
        $model = $this->findModel($id, $this->modelClass);
        return $model;
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
        if (empty($model)) {
            throw new NotFoundHttpException("Object not found: $id");
        }

        $this->checkAccess('update', $model, $id);

        $model->load(Yii::$app->getRequest()->getBodyParams(), '');

        if ($model->validate() && $model->save()) {
            $this->prepareDeleteAttachment($model->id);
            $this->prepareSaveAttachment($model->id);

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

    public function prepareDataProvider()
    {
        $params = Yii::$app->request->getQueryParams();
        $user = Yii::$app->user;

        $search = new NewsImportantSearch();

        if ($user->can('newsImportantManage') === true) {
            $search->scenario = NewsImportantSearch::SCENARIO_LIST_STAFF;
        }

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

        if (! empty($newsImportant)) {
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
}
