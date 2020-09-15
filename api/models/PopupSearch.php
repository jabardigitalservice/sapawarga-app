<?php

namespace app\models;

use Carbon\Carbon;
use app\components\ModelHelper;
use Illuminate\Support\Arr;
use yii\data\ActiveDataProvider;

/**
 * PopupSearch represents the model behind the search form of `app\models\Popup`.
 */
class PopupSearch extends Popup
{
    public const SCENARIO_LIST_STAFF = 'list-staff';
    public const SCENARIO_LIST_USER = 'list-user';
    public const LIMIT_LIST_USER = 1;

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Popup::find();
        $query->andFilterWhere(['<>', 'status', Popup::STATUS_DELETED]);

        if ($this->scenario === self::SCENARIO_LIST_STAFF) {
            return $this->getQueryListStaff($query, $params);
        }

        return $this->getQueryListUser($query, $params);
    }

    protected function getQueryListStaff($query, $params)
    {
        $query->andFilterWhere(['id' => $this->id]);
        $query->andFilterWhere(['like', 'title', Arr::get($params, 'title')]);
        $query->andFilterWhere(['=', 'type', Arr::get($params, 'type')]);

        if (Arr::has($params, 'status')) {
            $query->andFilterWhere(['status' => Arr::get($params, 'status')]);
        }

        return $this->getQueryAll($query, $params);
    }

    protected function getQueryListUser($query, $params)
    {
        $params['limit'] = self::LIMIT_LIST_USER;

        $todayDateTime = new Carbon();

        $query->andFilterWhere([
            'and',
            ['<=', 'start_date', $todayDateTime],
            ['>=', 'end_date', $todayDateTime],
        ]);

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
                    'type',
                    'status',
                    'start_date',
                    'end_date',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => $pageLimit,
            ],
        ]);
    }
}
