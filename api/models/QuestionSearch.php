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
        $query = Question::find()->with('comments')
                ->select([
                    '{{questions}}.*',
                    'COUNT({{likes}}.id) AS likes_count'
                ])
                ->joinWith('likes')
                ->groupBy('{{questions}}.id');

        $query->andFilterWhere(['<>', 'status', Question::STATUS_DELETED]);

        // Filtering
        $query->andFilterWhere(['id' => $this->id]);
        $query->andFilterWhere(['like', 'text',  Arr::get($params, 'search')]);
        $query->andFilterWhere(['status' => Arr::get($params, 'status')]);
        $query->andFilterWhere(['is_flagged' => Arr::get($params, 'is_flagged')]);

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
                ],
            ],
            'pagination' => [
                'pageSize' => $pageLimit,
            ],
        ]);
    }
}
