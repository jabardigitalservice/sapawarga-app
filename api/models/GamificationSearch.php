<?php

namespace app\models;

use Illuminate\Support\Arr;
use yii\data\ActiveDataProvider;
use app\components\ModelHelper;
use yii\db\ActiveQuery;
use yii\db\Expression;

/**
 * GamificationSearch represents the model behind the search form of `app\models\Gamification`.
 */
class GamificationSearch extends Gamification
{
    const SCENARIO_LIST_USER = 'list-user';
    const SCENARIO_LIST_STAFF = 'list-staff';

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Gamification::find();
        $query->where(['<>', 'status', Gamification::STATUS_DELETED]);

        // Filtering
        $query->andFilterWhere(['id' => $this->id]);
        $query->andFilterWhere(['like', 'title', Arr::get($params, 'title')]);


        if ($this->scenario === self::SCENARIO_LIST_USER) {
            return $this->getQueryListUser($query, $params);
        } else {
            return $this->getQueryListStaff($query, $params);
        }
    }

    protected function getQueryListUser($query, $params)
    {
        return $this->createActiveDataProvider($query, $params);
    }

    protected function getQueryListStaff($query, $params)
    {
        $query->andFilterWhere(['status' => Arr::get($params, 'status')]);

        if (Arr::get($params, 'start_date') && Arr::get($params, 'end_date')) {
            $query->andWhere([
                'and',
                ['<=', 'start_date', Arr::get($params, 'end_date')],
                ['>=', 'end_date', Arr::get($params, 'start_date')],
            ]);
        }

        return $this->createActiveDataProvider($query, $params);
    }

    protected function createActiveDataProvider($query, $params)
    {
        $pageLimit = Arr::get($params, 'limit');
        $sortBy    = Arr::get($params, 'sort_by', 'created_at');
        $sortOrder = Arr::get($params, 'sort_order', 'descending');
        $sortOrder = ModelHelper::getSortOrder($sortOrder);

        $provider = new ActiveDataProvider([
            'query'      => $query,
            'sort'       => [
                'defaultOrder' => [$sortBy => $sortOrder],
                'attributes' => [
                    'title',
                    'start_date',
                    'created_at',
                    'status',
                ],
            ],
            'pagination' => [
                'pageSize' => $pageLimit,
            ],
        ]);

        return $provider;
    }
}
