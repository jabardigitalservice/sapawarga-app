<?php

namespace app\models;

use app\components\ModelHelper;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use yii\data\ActiveDataProvider;

/**
 * SurveySearch represents the model behind the search form of `app\models\Survey`.
 */
class SurveySearch extends Survey
{
    const SCENARIO_LIST_USER = 'list-user';

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Survey::find()->with('category', 'kelurahan', 'kecamatan', 'kabkota');

        // grid filtering conditions
        $query->andFilterWhere(['id' => $this->id]);

        $query->andFilterWhere(['<>', 'status', Survey::STATUS_DELETED]);

        if ($this->scenario === self::SCENARIO_LIST_USER) {
            return $this->getQueryListUser($query, $params);
        }

        return $this->getQueryAll($query, $params);
    }

    protected function getQueryListUser($query, $params)
    {
        $filterStatusList = [
            Survey::STATUS_PUBLISHED
        ];

        $query->andFilterWhere(['in', 'status', $filterStatusList]);

        $today = new Carbon();

        $query->andFilterWhere(['<=', 'start_date', $today->toDateString()]);
        $query->andFilterWhere(['>=', 'end_date', $today->toDateString()]);

        return $this->getQueryAll($query, $params);
    }

    protected function getQueryAll($query, $params)
    {
        // Filter berdasarkan judul, status, dan kategori
        $query->andFilterWhere(['like', 'title', Arr::get($params, 'title')]);
        $query->andFilterWhere(['status' => Arr::get($params, 'status')]);
        $query->andFilterWhere(['category_id' => Arr::get($params, 'category_id')]);

        $pageLimit = Arr::get($params, 'limit');
        $sortBy    = Arr::get($params, 'sort_by', 'created_at');
        $sortOrder = Arr::get($params, 'sort_order', 'descending');
        $sortOrder = ModelHelper::getSortOrder($sortOrder);

        $provider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> ['defaultOrder' => [$sortBy => $sortOrder]],
            'pagination' => [
                'pageSize' => $pageLimit,
            ],
        ]);

        $provider->sort->attributes['category.name'] = [
            'asc'  => ['categories.name' => SORT_ASC],
            'desc' => ['categories.name' => SORT_DESC],
        ];

        return $provider;
    }
}
