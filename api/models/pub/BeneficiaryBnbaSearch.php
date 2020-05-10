<?php

namespace app\models\pub;

use app\components\ModelHelper;
use Illuminate\Support\Arr;
use yii\data\ActiveDataProvider;
use yii\data\SqlDataProvider;

/**
 * BeneficiaryBnbaSearch represents the model behind the search form of `app\models\pub\BeneficiaryBnba`.
 */
class BeneficiaryBnbaSearch extends BeneficiaryBnba
{
    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = BeneficiaryBnba::find()->where(['is_deleted' => null]);

        // Filtering
        $query->andFilterWhere(['kode_kab' => Arr::get($params, 'kabkota_bps_id')]);
        $query->andFilterWhere(['kode_kec' => Arr::get($params, 'kec_bps_id')]);
        $query->andFilterWhere(['kode_kel' => Arr::get($params, 'kel_bps_id')]);
        $query->andFilterWhere(['rw' => str_replace('RW ', '', Arr::get($params, 'rw'))]) ;
        $query->andFilterWhere(['rt' => Arr::get($params, 'rt')]);

        $query->andFilterWhere(['like', 'nama_krt', Arr::get($params, 'nama_krt')]);
        $query->andFilterWhere(['like', 'nik', Arr::get($params, 'nik')]);
        $query->andFilterWhere(['lapangan_usaha' => Arr::get($params, 'lapangan_usaha')]);
        $query->andFilterWhere(['id_tipe_bansos' => Arr::get($params, 'id_tipe_bansos')]);

        return $this->getQueryAll($query, $params);
    }

    public function getStatisticsByType($params)
    {
        $query = (new \yii\db\Query())
            ->select(['id_tipe_bansos', 'is_dtks', 'COUNT(id) AS total'])
            ->from('beneficiaries_bnba_tahap_1')
            ->where(['is_deleted' => null])
            ->groupBy(['id_tipe_bansos', 'is_dtks']);

        // Filtering Area
        if (! empty(Arr::get($params, 'kabkota_bps_id'))) {
            $query->andWhere(['=', 'kode_kab', Arr::get($params, 'kabkota_bps_id')]);
        }

        if (! empty(Arr::get($params, 'kec_bps_id'))) {
            $query->andWhere(['=', 'kode_kec', Arr::get($params, 'kec_bps_id')]);
        }

        if (! empty(Arr::get($params, 'kel_bps_id'))) {
            $query->andWhere(['=', 'kode_kel', Arr::get($params, 'kel_bps_id')]);
        }

        if (! empty(Arr::get($params, 'rw'))) {
            $query->andWhere(['=', 'rw', Arr::get($params, 'rw')]);
        }

        return $query->createCommand()->queryAll();
    }

    public function getStatisticsByArea($params)
    {
        $query = (new \yii\db\Query())
            ->select([$params['area_type'],'COUNT(id) AS total'])
            ->from('beneficiaries_bnba_tahap_1')
            ->where(['is_deleted' => null])
            ->groupBy([$params['area_type']]);

        // Filtering Area
        if (! empty(Arr::get($params, 'kabkota_bps_id'))) {
            $query->andWhere(['=', 'kode_kab', Arr::get($params, 'kabkota_bps_id')]);
        }

        if (! empty(Arr::get($params, 'kec_bps_id'))) {
            $query->andWhere(['=', 'kode_kec', Arr::get($params, 'kec_bps_id')]);
        }

        if (! empty(Arr::get($params, 'kel_bps_id'))) {
            $query->andWhere(['=', 'kode_kel', Arr::get($params, 'kel_bps_id')]);
        }

        if (! empty(Arr::get($params, 'rw'))) {
            $query->andWhere(['=', 'rw', Arr::get($params, 'rw')]);
        }

        return $query->createCommand()->queryAll();
    }

    protected function getQueryAll($query, $params)
    {
        $pageLimit = Arr::get($params, 'limit');
        $sortBy    = Arr::get($params, 'sort_by', 'nik');
        $sortOrder = Arr::get($params, 'sort_order', 'ascending');
        $sortOrder = ModelHelper::getSortOrder($sortOrder);

        return new ActiveDataProvider([
            'query'      => $query,
            'sort'       => [
                'defaultOrder' => [$sortBy => $sortOrder],
                'attributes' => [
                    'nama_krt',
                    'nik',
                    'lapangan_usaha',
                    'rt',
                    'id_tipe_bansos',
                    'rw',
                ],
            ],
            'pagination' => [
                'pageSize' => $pageLimit,
            ],
        ]);
    }
}
