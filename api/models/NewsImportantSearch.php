<?php

namespace app\models;

use Carbon\Carbon;
use app\components\ModelHelper;
use Illuminate\Support\Arr;
use yii\data\ActiveDataProvider;

/**
 * NewsImportantSearch represents the model behind the search form of `app\models\NewsImportant`.
 */
class NewsImportantSearch extends NewsImportant
{
    const SCENARIO_LIST_STAFF = 'list-staff';
    const SCENARIO_LIST_USER = 'list-user';
    const LIMIT_LIST_USER = 1;

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = NewsImportant::find();
        $query->andFilterWhere(['<>', 'status', NewsImportant::STATUS_DELETED]);

        if ($this->scenario === self::SCENARIO_LIST_STAFF) {
            return $this->getQueryListStaff($query, $params);
        }

        return $this->getQueryListUser($query, $params);
    }

    protected function getQueryListStaff($query, $params)
    {
        $query->andFilterWhere(['id' => $this->id]);
        $query->andFilterWhere(['like', 'title', Arr::get($params, 'title')]);
        $query->andFilterWhere(['=', 'category_id', Arr::get($params, 'category_id')]);

        if (Arr::has($params, 'status')) {
            $query->andFilterWhere(['status' => Arr::get($params, 'status')]);
        }

        return $this->getQueryAll($query, $params);
    }

    protected function getQueryListUser($query, $params)
    {
        $params['limit'] = self::LIMIT_LIST_USER;

        return $this->getQueryAll($query, $params);
    }

    protected function getQueryAll($query, $params)
    {
        $pageLimit = Arr::get($params, 'limit');
        $sortBy    = Arr::get($params, 'sort_by', 'created_at');
        $sortOrder = Arr::get($params, 'sort_order', 'descending');
        $sortOrder = ModelHelper::getSortOrder($sortOrder);

        return new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [$sortBy => $sortOrder],
                'attributes' => [
                    'title',
                    'category_id',
                    'status',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => $pageLimit,
            ],
        ]);
    }
}
