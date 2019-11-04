<?php

namespace app\modules\v1\controllers;

use app\models\Release;
use yii\filters\AccessControl;

/**
 * JobTypeController implements the CRUD actions for Release model.
 */
class JobTypeController extends ActiveController
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
            'class' => AccessControl::class,
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
}
