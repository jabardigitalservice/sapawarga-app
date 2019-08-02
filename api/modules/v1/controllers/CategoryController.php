<?php

namespace app\modules\v1\controllers;

use app\filters\auth\HttpBearerAuth;
use app\models\Category;
use app\models\CategorySearch;
use Yii;
use yii\filters\AccessControl;
use yii\filters\auth\CompositeAuth;

/**
 * CategoryController implements the CRUD actions for Category model.
 */
class CategoryController extends ActiveController
{
    public $modelClass = Category::class;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class'       => CompositeAuth::className(),
            'authMethods' => [
                HttpBearerAuth::className(),
            ],
        ];

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

        // remove authentication filter
        $auth = $behaviors['authenticator'];
        unset($behaviors['authenticator']);

        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::className(),
            'cors'  => [
                'Origin'                         => ['*'],
                'Access-Control-Request-Method'  => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
            ],
        ];

        // re-add authentication filter
        $behaviors['authenticator'] = $auth;
        // avoid authentication on CORS-pre-flight requests (HTTP OPTIONS method)
        $behaviors['authenticator']['except'] = ['options', 'public'];

        // setup access
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only'  => ['index', 'view', 'create', 'update', 'delete', 'types'], //only be applied to
            'rules' => [
                [
                    'allow'   => true,
                    'actions' => ['index', 'view', 'create', 'update', 'delete', 'types'],
                    'roles'   => ['admin', 'manageUsers'],
                ],
                [
                    'allow'   => true,
                    'actions' => ['index', 'view'],
                    'roles'   => ['user', 'staffRW'],
                ],
            ],
        ];

        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];
        return $actions;
    }

    public function actionTypes()
    {
        $model = Category::find()
            ->select('type as id')
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

    public function prepareDataProvider()
    {
        $search = new CategorySearch();

        return $search->search(\Yii::$app->request->getQueryParams());
    }
}
