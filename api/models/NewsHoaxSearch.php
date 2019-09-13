<?php

namespace app\models;

use app\components\ModelHelper;
use Illuminate\Support\Arr;
use yii\data\ActiveDataProvider;

/**
 * NewsHoaxSearch represents the model behind the search form of `app\models\NewsHoax`.
 */
class NewsHoaxSearch extends News
{
    const SCENARIO_LIST_USER = 'list-user';

    public $userRole;

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = NewsHoax::find()->joinWith('category');

        // grid filtering conditions
        $query->andFilterWhere(['id' => $this->id]);

        $filterCategoryId = Arr::get($params, 'category_id');
        $searchKeyword   = Arr::get($params, 'search');

        $query->andFilterWhere(['channel_id' => $filterCategoryId]);

        $query->andFilterWhere(['like', 'title', $searchKeyword]);

        $query->andFilterWhere(['<>', 'news_hoax.status', NewsHoax::STATUS_DELETED]);

        if ($this->scenario === self::SCENARIO_LIST_USER) {
            return $this->getQueryListUser($query, $params);
        }

        return $this->getQueryAll($query, $params);
    }

    protected function getQueryListUser($query, $params)
    {
        $filterStatusList = [
            NewsHoax::STATUS_ACTIVE,
        ];

        $query->andFilterWhere(['in', 'news_hoax.status', $filterStatusList]);

        return $this->getQueryAll($query, $params);
    }

    protected function getQueryAll($query, $params)
    {
        $pageLimit = Arr::get($params, 'limit');
        $sortBy    = Arr::get($params, 'sort_by', 'source_date');
        $sortOrder = Arr::get($params, 'sort_order', 'descending');
        $sortOrder = ModelHelper::getSortOrder($sortOrder);

        return new ActiveDataProvider([
            'query'      => $query,
            'sort'       => [
                'defaultOrder' => [$sortBy => $sortOrder],
                'attributes' => [
                    'title',
                    'source_date',
                    'seq',
                    'status',
                    'category.name' => [
                        'asc'  => ['categories.name' => SORT_ASC],
                        'desc' => ['categories.name' => SORT_DESC],
                    ],
                ],
            ],
            'pagination' => [
                'pageSize' => $pageLimit,
            ],
        ]);
    }
}
