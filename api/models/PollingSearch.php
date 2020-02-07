<?php

namespace app\models;

use app\components\ModelHelper;
use Illuminate\Support\Arr;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

/**
 * PollingSearch represents the model behind the search form of `app\models\Polling`.
 */
class PollingSearch extends Polling
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
    public function searchUser(array $params)
    {
        $query = Polling::find()->with('category', 'kelurahan', 'kecamatan', 'kabkota', 'answers');

        ModelHelper::filterCurrentActiveNow($query, $this);

        // Filter berdasarkan area pengguna
        $params['kabkota_id'] = Arr::get($params, 'kabkota_id');
        $params['kec_id']     = Arr::get($params, 'kec_id');
        $params['kel_id']     = Arr::get($params, 'kel_id');
        $params['rw']         = Arr::get($params, 'rw');

        $query = ModelHelper::filterByArea($query, $params);

        return $this->createActiveDataProvider($query, $params);
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function searchStaff($params)
    {
        $query = Polling::find()->with('category', 'kelurahan', 'kecamatan', 'kabkota', 'answers');

        $allLocation = Arr::get($params, 'all_location');

        // Filtering
        $query->andFilterWhere(['like', 'name', Arr::get($params, 'title')]);

        $query->andFilterWhere(['category_id' => Arr::get($params, 'category_id')]);

        $this->filterByStatus($query, $params);

        if ($allLocation == true) {
            $query->andWhere(
                ['and',
                ['is', 'kabkota_id', null],
                ['is', 'kec_id', null],
                ['is', 'kel_id', null]]
            );
        }

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
        // Filter started = status published + polling time started
        // Filter ended = status published + polling time ended

        $userId = $params['user_id'];

        if (Arr::has($params, 'status')) {
            $status = $params['status'];

            if ($status == Polling::STATUS_STARTED) {
                ModelHelper::filterCurrentActiveNow($query, $this);
            } elseif ($status == Polling::STATUS_ENDED) {
                ModelHelper::filterIsEnded($query, $this);
            } elseif ($status == Polling::STATUS_DRAFT) {
                $this->filterOwnDraft($query, $userId);
            }
        } else {
            $query->andFilterWhere([
                'or',
                ['>', 'status', Polling::STATUS_DRAFT],
                [
                    'and',
                    ['status' => Polling::STATUS_DRAFT],
                    ['created_by' => $userId],
                ]
            ]);
        }

        return $query;
    }

    protected function filterOwnDraft($query, $userId)
    {
        $query->andFilterWhere([
            'and',
            ['status' => Polling::STATUS_DRAFT],
            ['created_by' => $userId],
        ]);
    }
}
