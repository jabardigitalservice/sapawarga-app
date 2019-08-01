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
    /**
     * @var \app\models\User
     */
    public $user;

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
        $this->filterByArea($query, $params);

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

    protected function filterByArea(&$query, $params)
    {
        if (Arr::has($params, 'kabkota_id') || Arr::has($params, 'kec_id') || Arr::has($params, 'kel_id')) {
            ModelHelper::filterByAreaTopDown($query, $params);
        } else {
            // By default filter berdasarkan area Staf tersebut
            $areaParams = [
            'kabkota_id' => $this->user->kabkota_id ?? null,
            'kec_id' => $this->user->kec_id ?? null,
            'kel_id' => $this->user->kel_id ?? null,
            ];
            ModelHelper::filterByAreaTopDown($query, $areaParams);
        }
    }
}
