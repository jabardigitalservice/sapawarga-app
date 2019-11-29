<?php

namespace app\modules\v1\controllers;

use app\models\Category;
use app\models\CategorySearch;
use Illuminate\Support\Arr;
use Yii;
use yii\filters\AccessControl;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * CategoryController implements the CRUD actions for Category model.
 */
class CategoryController extends ActiveController
{
    public $modelClass = Category::class;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['verbs'] = [
            'class'   => \yii\filters\VerbFilter::className(),
            'actions' => [
                'index'  => ['get'],
                'view'   => ['get'],
                'create' => ['post'],
                'update' => ['put'],
                'delete' => ['delete'],
                'public' => ['get'],
                'types'  => ['get'],
            ],
        ];

        return $this->behaviorCors($behaviors);
    }

    protected function behaviorAccess($behaviors)
    {
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only'  => ['index', 'view', 'create', 'update', 'delete', 'types'], //only be applied to
            'rules' => [
                [
                    'allow'   => true,
                    'actions' => ['index', 'view', 'create', 'update', 'delete', 'types'],
                    'roles'   => ['admin', 'staffProv'],
                ],
                [
                    'allow'   => true,
                    'actions' => ['index', 'view'],
                    'roles'   => ['user', 'staffRW', 'newsSaberhoaxManage'],
                ],
            ],
        ];

        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();

        // Override actions related to edit
        unset($actions['create']);
        unset($actions['update']);
        unset($actions['delete']);

        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];
        $actions['view']['findModel']            = [$this, 'findModel'];

        return $actions;
    }

    public function actionCreate()
    {
        $model = new Category();

        $model->load(Yii::$app->getRequest()->getBodyParams(), '');

        $this->checkAccess('create', $model);

        if ($model->validate() && $model->save()) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(201);
        } else {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(422);

            return $model->getErrors();
        }

        return $model;
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $params = Yii::$app->getRequest()->getBodyParams();

        $this->checkAccess('update', $model, $params);

        $model->load($params, '');

        if ($model->validate() && $model->save()) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(200);
        } else {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(422);

            return $model->getErrors();
        }

        return $model;
    }

    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        $this->checkAccess('delete', $model);

        return $this->applySoftDelete($model);
    }

    public function actionTypes()
    {
        $model = Category::find()
            ->select('type as id')
            ->where(['not in', 'type', Category::EXCLUDED_TYPES])
            ->groupBy('type')
            ->asArray()
            ->all();

        foreach ($model as &$type) {
            $type['name'] = Category::TYPE_MAP[$type['id']];
        }

        $name = array_column($model, 'name');
        array_multisort($name, SORT_ASC, $model);

        $response = Yii::$app->getResponse();
        $response->setStatusCode(200);
        return [ 'items' => $model ];
    }

    /**
     * @param $id
     * @return mixed|Category
     * @throws \yii\web\NotFoundHttpException
     */
    public function findModel($id)
    {
        $model = Category::find()
            ->where(['id' => $id])
            ->andWhere(['!=', 'status', Category::STATUS_DELETED])
            ->one();

        if ($model === null) {
            throw new NotFoundHttpException("Object not found: $id");
        }

        return $model;
    }

    public function prepareDataProvider()
    {
        $search = new CategorySearch();

        return $search->search(\Yii::$app->request->getQueryParams());
    }

    /**
     * @param string $action the ID of the action to be executed
     * @param object $model the model to be accessed. If null, it means no specific model is being accessed.
     * @param array $params additional parameters
     * @throws \yii\web\ForbiddenHttpException
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        switch ($action) {
            case 'create':
            case 'delete':
                if (in_array($model->type, Category::EXCLUDED_TYPES)) {
                    throw new ForbiddenHttpException(Yii::t('app', 'error.role.permission'));
                }
                break;
            case 'update':
                if (in_array($model->type, Category::EXCLUDED_TYPES)
                    || in_array(Arr::get($params, 'type'), Category::EXCLUDED_TYPES)) {
                    throw new ForbiddenHttpException(Yii::t('app', 'error.role.permission'));
                }
                break;
            default:
                break;
        }
    }
}
