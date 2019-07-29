<?php

namespace app\models;

use app\components\ModelHelper;
use Carbon\Carbon;
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
    public function search($params)
    {
        $query = Polling::find()->with('category', 'kelurahan', 'kecamatan', 'kabkota', 'answers');

        // grid filtering conditions
        $query->andFilterWhere(['id' => $this->id]);

        $query->andFilterWhere(['<>', 'status', Polling::STATUS_DELETED]);

        if ($this->scenario === self::SCENARIO_LIST_USER) {
            return $this->getQueryListUser($query, $params);
        }

        if ($this->scenario === self::SCENARIO_LIST_STAFF) {
            return $this->getQueryListStaff($query, $params);
        }
    }

    protected function getQueryListUser($query, $params)
    {
        $filterStatusList = [
            Survey::STATUS_PUBLISHED,
        ];

        $query->andFilterWhere(['in', 'status', $filterStatusList]);

        $this->filterCurrentActiveNow($query);

        $this->filterByUserArea($query, $params);

        return $this->createActiveDataProvider($query, $params);
    }

    protected function getQueryListStaff($query, $params)
    {
        // Filtering
        $query->andFilterWhere(['like', 'name', Arr::get($params, 'title')]);

        $query->andFilterWhere(['category_id' => Arr::get($params, 'category_id')]);

        if (Arr::get($params, 'status') == Polling::STATUS_STARTED) {
            $this->filterCurrentActiveNow($query);
        } elseif (Arr::get($params, 'status') == Polling::STATUS_ENDED) {
            $this->filterIsEnded($query);
        } else {
            $query->andFilterWhere(['status' => Arr::get($params, 'status')]);
        }

        // Filtering by role staff
        $query = $this->filterByStaffArea($query, $params);

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

    protected function filterCurrentActiveNow($query)
    {
        $today = new Carbon();
        $query->andFilterWhere(['<=', 'start_date', $today->toDateString()]);
        $query->andFilterWhere(['>=', 'end_date', $today->toDateString()]);

        return $query;
    }

    protected function filterIsEnded($query)
    {
        $today = new Carbon();
        $query->andFilterWhere(['<', 'end_date', $today->toDateString()]);

        return $query;
    }

    protected function filterByUserArea(&$query, $params)
    {
        $kabKotaId = Arr::get($params, 'kabkota_id');
        $kecId     = Arr::get($params, 'kec_id');
        $kelId     = Arr::get($params, 'kel_id');
        $rw        = Arr::get($params, 'rw');

        $query->andWhere('
            (kabkota_id = :kabkota_id AND kec_id IS NULL AND kel_id IS NULL AND rw IS NULL) OR
            (kabkota_id = :kabkota_id AND kec_id = :kec_id AND kel_id IS NULL AND rw IS NULL) OR
            (kabkota_id = :kabkota_id AND kec_id = :kec_id AND kel_id = :kel_id AND rw IS NULL) OR
            (kabkota_id = :kabkota_id AND kec_id = :kec_id AND kel_id = :kel_id AND rw = :rw)', [

            ':kabkota_id' => $kabKotaId,
            ':kec_id'     => $kecId,
            ':kel_id'     => $kelId,
            ':rw'         => $rw,
        ]);

        return $query;
    }

    protected function filterByStaffArea(ActiveQuery $query, $params)
    {
        if (Arr::has($params, 'kabkota_id')) {
            $query->andWhere(['or',
                ['kabkota_id' => $params['kabkota_id']],
                ['kabkota_id' => null]]);
        }

        if (Arr::has($params, 'kec_id')) {
            $query->andWhere(['or',
                ['kec_id' => $params['kec_id']],
                ['kec_id' => null]]);
        }

        if (Arr::has($params, 'kel_id')) {
            $query->andWhere(['or',
                ['kel_id' => $params['kel_id']],
                ['kel_id' => null]]);
        }

        if (Arr::has($params, 'rw')) {
            $query->andWhere(['or',
                ['rw' => $params['rw']],
                ['rw' => null]]);
        }

        return $query;
    }
}
