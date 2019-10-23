<?php

namespace app\models;

use app\components\ModelHelper;
use Illuminate\Support\Arr;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

/**
 * BroadcastSearch represents the model behind the search form of `app\models\Broadcast`.
 * @property int $user_id
 */
class BroadcastSearch extends Model
{
    public $user_id;

    const SCENARIO_LIST_STAFF_DEFAULT = 'list-staff-default';

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function searchStaff($params)
    {
        $query = Broadcast::find();

        // Filter berdasarkan query pencarian
        $title = Arr::get($params, 'title');

        $query->andFilterWhere(['like', 'title', $title]);

        // Filter berdasarkan status dan kategori
        $this->filterByStatus($query, $params);

        $query->andFilterWhere(['category_id' => Arr::get($params, 'category_id')]);

        $query = ModelHelper::filterByArea($query, $params);

        return $this->createActiveDataProvider($query, $params);
    }

    protected function createActiveDataProvider(ActiveQuery $query, array $params)
    {
        $pageLimit = Arr::get($params, 'limit');
        $sortBy    = Arr::get($params, 'sort_by', 'updated_at');
        $sortOrder = Arr::get($params, 'sort_order', 'descending');
        $sortOrder = ModelHelper::getSortOrder($sortOrder);

        return new ActiveDataProvider([
            'query' => $query,
            'sort'  => ['defaultOrder' => [$sortBy => $sortOrder]],
            'pagination' => [
                'pageSize' => $pageLimit,
            ],
        ]);
    }

    /**
     * Filters query by status
     *
     * @param &$query
     * @param $params
     */
    private function filterByStatus(&$query, $params)
    {
        $model = Broadcast::class;

        // Tidak mengikutsertakan STATUS_DELETED
        $query->andFilterWhere(['<>', 'status', $model::STATUS_DELETED]);

        if (Arr::has($params, 'status')) {
            if ($params['status'] == $model::STATUS_DRAFT) {
                $query->andFilterWhere([
                    'and',
                    ['status' => $params['status']],
                    ['author_id' => $this->user_id],
                ]);
            } else {
                $query->andFilterWhere(['status' => $params['status']]);
            }
        } else {
            $query->andFilterWhere([
                'or',
                ['status' => Broadcast::STATUS_PUBLISHED],
                ['status' => Broadcast::STATUS_SCHEDULED],
                [
                    'and',
                    ['status' => Broadcast::STATUS_DRAFT],
                    ['author_id' => $this->user_id],
                ]
            ]);
        }

        return $query;
    }
}
