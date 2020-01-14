<?php

namespace app\models;

use app\components\ModelHelper;
use Illuminate\Support\Arr;
use yii\data\ActiveDataProvider;

/**
 * QuestionSearch represents the model behind the search form of `app\models\Question`.
 */
class QuestionSearch extends Question
{
    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $subQueryLikesCount = Like::find()->select(['entity_id', 'count(id) likes_count'])
                                ->where(['type' => 'question'])
                                ->groupBy('entity_id')
                                ->createCommand()->getRawSql();

        $subQueryCommentCount = QuestionComment::find()->select(['question_id', 'count(id) comments_count'])
                                ->where(['is_flagged' => 0])
                                ->groupBy('question_id')
                                ->createCommand()->getRawSql();

        $query = Question::find()
                ->select([
                    '{{questions}}.*',
                    'ifnull(likes_count, 0) as likes_count',
                    'ifnull(comments_count, 0) as comments_count'
                ])
                ->leftJoin("($subQueryLikesCount) as likes", 'likes.entity_id = questions.id')
                ->leftJoin("($subQueryCommentCount) as comments", 'comments.question_id = questions.id')
                ->where(['<>', 'questions.status', Question::STATUS_DELETED]);

        // Filtering
        $query->andFilterWhere(['like', 'text',  Arr::get($params, 'search')]);
        $query->andFilterWhere(['is_flagged' => Arr::get($params, 'is_flagged')]);
        $query->andFilterWhere(['questions.status' => Arr::get($params, 'status')]);
        return $this->getQueryAll($query, $params);
    }

    protected function getQueryAll($query, $params)
    {
        $pageLimit = Arr::get($params, 'limit');
        $sortBy    = Arr::get($params, 'sort_by', 'likes_count');
        $sortOrder = Arr::get($params, 'sort_order', 'descending');
        $sortOrder = ModelHelper::getSortOrder($sortOrder);

        return new ActiveDataProvider([
            'query'      => $query,
            'sort'       => [
                'defaultOrder' => [$sortBy => $sortOrder],
                'attributes' => [
                    'text',
                    'created_at',
                    'status',
                    'likes_count',
                    'comments_count',
                ],
            ],
            'pagination' => [
                'pageSize' => $pageLimit,
            ],
        ]);
    }
}
