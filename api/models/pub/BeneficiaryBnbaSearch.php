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
        $query = BeneficiaryBnba::find()->andWhere(['or', ['is_deleted' => null], ['is_deleted' => 0]]);

        // Filtering
        $query->andFilterWhere(['nik' => Arr::get($params, 'nik')]);
        $query->andFilterWhere(['tahap_bantuan' => Arr::get($params, 'tahap')]);
        if (empty(Arr::get($params, 'id_tipe_bansos'))) {
            $query->andFilterWhere(['and', ['>', 'id_tipe_bansos', 0], ['<', 'id_tipe_bansos', 9] ]);
        } else {
            $query->andFilterWhere(['id_tipe_bansos' => ltrim(Arr::get($params, 'id_tipe_bansos'), '0')]);
        }

        $query->andFilterWhere(['kode_kab' => Arr::get($params, 'kode_kab')]);
        $query->andFilterWhere(['kode_kec' => Arr::get($params, 'kode_kec')]);
        $query->andFilterWhere(['kode_kel' => Arr::get($params, 'kode_kel')]);

        $query->andFilterWhere(['kode_kab' => Arr::get($params, 'kabkota_bps_id')]);
        $query->andFilterWhere(['kode_kec' => Arr::get($params, 'kec_bps_id')]);
        $query->andFilterWhere(['kode_kel' => Arr::get($params, 'kel_bps_id')]);

        if (Arr::get($params, 'rw') == 'Tidak') {
            $query->andWhere(['rw' => null]);
        } else {
            $query->andFilterWhere(['rw' => str_replace('RW ', '', Arr::get($params, 'rw'))]);
        }

        $query->andFilterWhere(['rt' => Arr::get($params, 'rt')]);
        $query->andFilterWhere(['like', 'nama_krt', Arr::get($params, 'nama_krt')]);
        $query->andFilterWhere(['lapangan_usaha' => Arr::get($params, 'lapangan_usaha')]);

        return $this->getQueryAll($query, $params);
    }

    public function getStatisticsByType($params)
    {
        $query = (new \yii\db\Query())
            ->select(['id_tipe_bansos', 'is_dtks', 'COUNT(id) AS total'])
            ->from('beneficiaries_bnba_tahap_1')
            ->andWhere(['or',
                ['is_deleted' => null],
                ['is_deleted' => 0]
            ])
            ->groupBy(['id_tipe_bansos', 'is_dtks']);

        // Filtering Area
        if (empty(Arr::get($params, 'id_tipe_bansos'))) {
            $query->andFilterWhere(['and', ['>', 'id_tipe_bansos', 0], ['<', 'id_tipe_bansos', 9] ]);
        } else {
            $query->andFilterWhere(['id_tipe_bansos' => ltrim(Arr::get($params, 'id_tipe_bansos'), '0')]);
        }
        $query->andFilterWhere(['=', 'tahap_bantuan', Arr::get($params, 'tahap')]);
        $query->andFilterWhere(['=', 'kode_kab', Arr::get($params, 'kabkota_bps_id')]);
        $query->andFilterWhere(['=', 'kode_kec', Arr::get($params, 'kec_bps_id')]);
        $query->andFilterWhere(['=', 'kode_kel', Arr::get($params, 'kel_bps_id')]);

        if (Arr::get($params, 'rw') == 'Tidak') {
            $query->andWhere(['rw' => null]);
        } else {
            $query->andFilterWhere(['rw' => str_replace('RW ', '', Arr::get($params, 'rw'))]);
        }

        return $query->createCommand()->queryAll();
    }

    public function getStatisticsByArea($params)
    {
        $query = (new \yii\db\Query())
            ->select([$params['area_type'],'COUNT(id) AS total'])
            ->from('beneficiaries_bnba_tahap_1')
            ->andWhere(['or',
                ['is_deleted' => null],
                ['is_deleted' => 0]
            ])
            ->groupBy([$params['area_type']]);

        // Filtering Area
        if (empty(Arr::get($params, 'id_tipe_bansos'))) {
            $query->andFilterWhere(['and', ['>', 'id_tipe_bansos', 0], ['<', 'id_tipe_bansos', 9] ]);
        } else {
            $query->andFilterWhere(['id_tipe_bansos' => ltrim(Arr::get($params, 'id_tipe_bansos'), '0')]);
        }
        $query->andFilterWhere(['=', 'tahap_bantuan', Arr::get($params, 'tahap')]);
        $query->andFilterWhere(['=', 'kode_kab', Arr::get($params, 'kabkota_bps_id')]);
        $query->andFilterWhere(['=', 'kode_kec', Arr::get($params, 'kec_bps_id')]);
        $query->andFilterWhere(['=', 'kode_kel', Arr::get($params, 'kel_bps_id')]);
        $query->andFilterWhere(['=', 'rw', Arr::get($params, 'rw')]);

        return $query->createCommand()->queryAll();
    }

    protected function getQueryAll($query, $params)
    {
        $pageLimit = Arr::get($params, 'limit');

        return new ActiveDataProvider([
            'query'      => $query,
            'pagination' => [
                'pageSize' => $pageLimit,
            ],
        ]);
    }
}
