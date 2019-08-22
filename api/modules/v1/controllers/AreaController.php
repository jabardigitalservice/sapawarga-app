<?php

namespace app\modules\v1\controllers;

use app\filters\auth\HttpBearerAuth;
use app\models\Area;
use app\models\AreaSearch;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\filters\auth\CompositeAuth;
use yii\helpers\Url;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

/**
 * AreaController implements the CRUD actions for Area model.
 */
class AreaController extends ActiveController
{
    public $modelClass = Area::class;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            'authMethods' => [
                HttpBearerAuth::className(),
            ],
        ];

        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::className(),
            'actions' => [
                'index' => ['get'],
                'view' => ['get'],
                'create' => ['post'],
                'update' => ['put'],
                'delete' => ['delete'],
                'public' => ['get'],
            ],
        ];

        return $this->behaviorCors($behaviors);
    }

    protected function behaviorAccess($behaviors)
    {
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => ['index', 'view', 'create', 'update', 'delete'], //only be applied to
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['index', 'view', 'create', 'update', 'delete'],
                    'roles' => ['areaManage'],
                ],
                [
                    'allow' => true,
                    'actions' => ['index', 'view'],
                    'roles' => ['areaList'],
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

    public function prepareDataProvider()
    {
        $search = new AreaSearch();

        return $search->search(\Yii::$app->request->getQueryParams());
    }
}
