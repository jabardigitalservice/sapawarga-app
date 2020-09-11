<?php

namespace app\models;

use app\components\ModelHelper;
use Illuminate\Support\Arr;
use yii\data\ActiveDataProvider;

/**
 * BeneficiarySearch represents the model behind the search form of `app\models\Beneficiary`.
 */
class BeneficiaryBnbaTahapSatuSearch extends Beneficiary
{
    public $userRole;

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = BeneficiaryBnbaTahapSatu::find();

        // Filtering
        $query->where(['or', ['is_deleted' => null], ['is_deleted' => 0] ]);
        $query->andFilterWhere(['id' => $this->id]);
        $query->andFilterWhere(['nik' => Arr::get($params, 'nik')]);
        $query->andFilterWhere(['no_kk' => Arr::get($params, 'no_kk')]);

        if (Arr::get($params, 'nama_krt')) {
            $query->andFilterWhere(['like', 'nama_krt', Arr::get($params, 'nama_krt') . '%', false]);
        }

        if (empty(Arr::get($params, 'id_tipe_bansos'))) {
            $query->andFilterWhere(['and', ['>', 'id_tipe_bansos', 0], ['<', 'id_tipe_bansos', 9] ]);
        } else {
            $query->andFilterWhere(['id_tipe_bansos' => ltrim(Arr::get($params, 'id_tipe_bansos'), '0')]);
        }

        if (empty(Arr::get($params, 'tahap_bantuan'))) {
            $query->andFilterWhere(['or', ['tahap_bantuan' => 1], ['tahap_bantuan' => 2] ]);
        } else {
            $query->andFilterWhere(['tahap_bantuan' => Arr::get($params, 'tahap')]);
        }

        $query->andFilterWhere(['kode_kab' => Arr::get($params, 'kode_kab')]);
        $query->andFilterWhere(['kode_kec' => Arr::get($params, 'kode_kec')]);
        $query->andFilterWhere(['kode_kel' => Arr::get($params, 'kode_kel')]);
        $query->andFilterWhere(['`rw` * 1' => ltrim(Arr::get($params, 'rw'), '0')]);
        $query->andFilterWhere(['rt' => ltrim(Arr::get($params, 'rt'), '0')]);

        return $this->getQueryAll($query, $params);
    }

    public function getSummaryByType($params)
    {
        $query = (new \yii\db\Query())
            ->select(['id_tipe_bansos', 'COUNT(id) AS total'])
            ->from('beneficiaries_bnba_tahap_1')
            ->groupBy(['id_tipe_bansos']);

        // Filtering Area
        $query->where(['or', ['is_deleted' => null], ['is_deleted' => 0] ]);
        if (empty(Arr::get($params, 'id_tipe_bansos'))) {
            $query->andFilterWhere(['and', ['>', 'id_tipe_bansos', 0], ['<', 'id_tipe_bansos', 9] ]);
        } else {
            $query->andFilterWhere(['id_tipe_bansos' => ltrim(Arr::get($params, 'id_tipe_bansos'), '0')]);
        }
        $query->andFilterWhere(['=', 'tahap_bantuan', Arr::get($params, 'tahap')]);
        $query->andFilterWhere(['=', 'kode_kab', Arr::get($params, 'kode_kab')]);
        $query->andFilterWhere(['=', 'kode_kec', Arr::get($params, 'kode_kec')]);
        $query->andFilterWhere(['=', 'kode_kel', Arr::get($params, 'kode_kel')]);

        return $query->createCommand()->queryAll();
    }

    protected function getQueryAll($query, $params)
    {
        $pageLimit = Arr::get($params, 'limit');
        $sortBy    = Arr::get($params, 'sort_by', 'nik');
        $sortOrder = Arr::get($params, 'sort_order', 'ascending');
        $sortOrder = ModelHelper::getSortOrder($sortOrder);

        $defaultOrder = [ $sortBy => $sortOrder ];
        if ($this->userRole == User::ROLE_STAFF_RW || $this->userRole == User::ROLE_TRAINER) {
            $defaultOrder = [ 'rw' => SORT_ASC, 'rt' => SORT_ASC ] + $defaultOrder;
        }

        return new ActiveDataProvider([
            'query'      => $query,
            'sort'       => [
                'attributes' => [
                    'nama_krt',
                    'nik',
                    'nama_kab',
                    'lapangan_usaha',
                    'rt',
                    'rw',
                    'penghasilan_sebelum_covid19',
                    'penghasilan_sesudah_covid19',
                    'id_tipe_bansos',
                    'created_at',
                    'updated_at',
                ],
            ],
            'pagination' => [
                'pageSize' => $pageLimit,
            ],
        ]);
    }
}
