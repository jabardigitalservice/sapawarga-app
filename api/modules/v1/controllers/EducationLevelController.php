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
            'items' => include __DIR__ . '/../../../config/references/education_level.php',
        ];
    }
}
