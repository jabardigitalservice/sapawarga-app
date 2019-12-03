<?php

namespace app\modules\v1\controllers;

use app\models\QuestionComment;
use yii\filters\AccessControl;

class QuestionCommentController extends ActiveController
{
    public $modelClass = QuestionComment::class;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        return $this->behaviorCors($behaviors);
    }

    protected function behaviorAccess($behaviors)
    {
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'only'  => ['index', 'view', 'create'],
            'rules' => [
                [
                    'allow'   => true,
                    'actions' => ['index', 'view', 'create'],
                    'roles'   => ['admin'],
                ],
            ],
        ];

        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();

        unset($actions['view']);

        return $actions;
    }

    public function actionView($questionId, $id)
    {
        return ['question_id' => $questionId, 'id' => $id];
    }
}
