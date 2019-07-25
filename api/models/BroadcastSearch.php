<?php

namespace app\models;

use Illuminate\Support\Arr;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

/**
 * BroadcastSearch represents the model behind the search form of `app\models\Broadcast`.
 */
class BroadcastSearch extends Model
{
    const SCENARIO_LIST_USER_DEFAULT  = 'list-user-default';
    const SCENARIO_LIST_STAFF_DEFAULT = 'list-staff-default';


    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function searchUser(array $params)
    {
        $query = Broadcast::find();

        // Hanya menampilkan pesan broadcast yang masih aktif
        $query->andFilterWhere(['status' => Broadcast::STATUS_PUBLISHED]);

        // Hanya menampilkan pesan broadcast yang di-publish setelah user melakukan login
        $startDatetime = Arr::get($params, 'start_datetime');

        $query->andFilterWhere(['>=', 'updated_at', $startDatetime]);

        // Filter berdasarkan area pengguna
        $params['kabkota_id'] = Arr::get($params, 'kabkota_id');
        $params['kec_id']     = Arr::get($params, 'kec_id');
        $params['kel_id']     = Arr::get($params, 'kel_id');
        $params['rw']         = Arr::get($params, 'rw');

        $query = $this->filterByUserArea($query, $params); // @TODO Refactor pakai ModelHelper

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
        $query = Broadcast::find();

        // Filter berdasarkan query pencarian
        $title = Arr::get($params, 'title');

        $query->andFilterWhere(['like', 'title', $title]);

        // Filter berdasarkan status dan kategori
        $query->andFilterWhere(['status' => Arr::get($params, 'status')]);
        $query->andFilterWhere(['category_id' => Arr::get($params, 'category_id')]);

        // Hanya menampilkan pesan broadcast dengan status aktif dan draft
        $query->andFilterWhere(['<>', 'status', Broadcast::STATUS_DELETED]);

        $query = $this->filterByUserArea($query, $params); // @TODO Refactor pakai ModelHelper

        return $this->createActiveDataProvider($query, $params);
    }

    protected function createActiveDataProvider(ActiveQuery $query, array $params)
    {
        $pageLimit = Arr::get($params, 'limit');
        $sortBy    = Arr::get($params, 'sort_by', 'updated_at');
        $sortOrder = Arr::get($params, 'sort_order', 'descending');
        $sortOrder = $this->getSortOrder($sortOrder);

        return new ActiveDataProvider([
            'query' => $query,
            'sort'  => ['defaultOrder' => [$sortBy => $sortOrder]],
            'pagination' => [
                'pageSize' => $pageLimit,
            ],
        ]);
    }

    protected function filterByUserArea(ActiveQuery $query, $params)
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

    protected function getSortOrder($sortOrder)
    {
        switch ($sortOrder) {
            case 'descending':
                return SORT_DESC;
                break;
            case 'ascending':
            default:
                return SORT_ASC;
                break;
        }
    }
}
