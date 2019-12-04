<?php

namespace app\modules\v1\controllers;

use app\components\ModelHelper;
use app\models\QuestionComment;
use Illuminate\Support\Arr;
use Yii;
use yii\data\ActiveDataProvider;
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
                    'roles'   => ['admin', 'staffProv'],
                ],
            ],
        ];

        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();

        unset($actions['index']);
        unset($actions['view']);

        return $actions;
    }

    public function actionIndex()
    {
        $params = Yii::$app->request->getQueryParams();

        $query = QuestionComment::find();
        $query->andWhere(['question_id' => Arr::get($params, 'questionId')]);
        $query->andWhere(['status' => QuestionComment::STATUS_ACTIVE]);

        $pageLimit = Arr::get($params, 'limit');
        $sortBy    = Arr::get($params, 'sort_by', 'created_at');
        $sortOrder = Arr::get($params, 'sort_order', 'ascending');
        $sortOrder = ModelHelper::getSortOrder($sortOrder);

        return new ActiveDataProvider([
            'query'      => $query,
            'sort'       => [
                'defaultOrder' => [$sortBy => $sortOrder],
                'attributes' => [
                    'title',
                    'status',
                    'created_at'
                ],
            ],
            'pagination' => [
                'pageSize' => $pageLimit,
            ],
        ]);
    }

    public function actionView($questionId, $id)
    {
        return ['question_id' => $questionId, 'id' => $id];
    }
}
