<?php

namespace app\modules\v1\controllers;

use app\models\EducationLevel;
use Yii;
use yii\filters\AccessControl;

/**
 * EducationLevelController implements the CRUD actions for Release model.
 */
class EducationLevelController extends ActiveController
{
    public $modelClass = EducationLevel::class;

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

        // Override Index Action
        unset($actions['index']);

        return $actions;
    }

    public function actionIndex()
    {
        return [
            'items' => [
                ['id' => 1, 'name' => 'Tidak Ada', 'seq' => 0],
                ['id' => 2, 'name' => 'SD/MI', 'seq' => 1],
                ['id' => 3, 'name' => 'SMP/MTS', 'seq' => 1],
                ['id' => 4, 'name' => 'SMA/SMK', 'seq' => 1],
                ['id' => 5, 'name' => 'D1', 'seq' => 1],
                ['id' => 6, 'name' => 'D2', 'seq' => 1],
                ['id' => 7, 'name' => 'D3', 'seq' => 1],
                ['id' => 8, 'name' => 'S1', 'seq' => 1],
                ['id' => 9, 'name' => 'S2', 'seq' => 1],
                ['id' => 10, 'name' => 'S3', 'seq' => 1],
                ['id' => 11, 'name' => 'Lainnya', 'seq' => 99],
            ],
        ];
    }
}
