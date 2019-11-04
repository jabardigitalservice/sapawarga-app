<?php

namespace app\modules\v1\controllers;

use app\models\JobType;
use app\models\JobTypeSearch;
use Yii;
use yii\filters\AccessControl;

/**
 * JobTypeController implements the CRUD actions for Release model.
 */
class JobTypeController extends ActiveController
{
    public $modelClass = JobType::class;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        return $this->behaviorCors($behaviors);
    }

    protected function behaviorAccess($behaviors)
    {
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'only'  => ['index', 'view', 'create', 'update', 'delete'],
            'rules' => [
                [
                    'allow'   => true,
                    'actions' => ['index'],
                    'roles'   => ['@'],
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
        $params = Yii::$app->request->getQueryParams();
        $search = new JobTypeSearch();

        return $search->search($params);
    }
}
