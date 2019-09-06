<?php

namespace app\modules\v1\controllers;

use app\models\Release;
use app\models\ReleaseSearch;
use yii\filters\AccessControl;

/**
 * ReleaseController implements the CRUD actions for Release model.
 */
class ReleaseController extends ActiveController
{
    public $modelClass = Release::class;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        return $this->behaviorCors($behaviors);
    }

    protected function behaviorAccess($behaviors)
    {
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only'  => ['index', 'view', 'create', 'update', 'delete'],
            'rules' => [
                [
                    'allow'   => true,
                    'actions' => ['index', 'view', 'create', 'update', 'delete'],
                    'roles'   => ['admin'],
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
        $search = new ReleaseSearch();

        return $search->search(\Yii::$app->request->getQueryParams());
    }
}
