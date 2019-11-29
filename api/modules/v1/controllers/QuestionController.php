<?php

namespace app\modules\v1\controllers;

use app\models\Question;
use yii\filters\AccessControl;

/**
 * QuestionController implements the CRUD actions for Release model.
 */
class QuestionController extends ActiveController
{
    public $modelClass = Question::class;

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
}
