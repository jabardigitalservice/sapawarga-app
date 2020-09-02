<?php

namespace app\models;

use app\components\ModelHelper;
use Illuminate\Support\Arr;
use yii\data\ActiveDataProvider;

/**
 * BeneficiaryComplainSearch represents the model behind the search form of `app\models\BeneficiaryComplain`.
 */
class BeneficiaryComplainSearch extends BeneficiaryComplain
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
        $query = BeneficiaryComplain::find()->joinWith('beneficiaryBnba')
                    ->where(['=', 'beneficiaries_complain.status', BeneficiaryComplain::STATUS_ACTIVE]);

        // Filtering
        $query->andFilterWhere(['id' => $this->id]);
        // $query->andFilterWhere(['like', 'beneficiaries_bnba_tahap_1.nama_krt', Arr::get($params, 'name')]);
        $query->andFilterWhere(['=', 'beneficiaries_bnba_tahap_1.nik', Arr::get($params, 'nik')]);
        $query->andFilterWhere(['=', 'beneficiaries_bnba_tahap_1.kode_kab', Arr::get($params, 'kode_kab')]);
        $query->andFilterWhere(['=', 'beneficiaries_bnba_tahap_1.kode_kec', Arr::get($params, 'kode_kec')]);
        $query->andFilterWhere(['=', 'beneficiaries_bnba_tahap_1.kode_kel', Arr::get($params, 'kode_kel')]);
        $query->andFilterWhere(['=', 'beneficiaries_bnba_tahap_1.rw', Arr::get($params, 'rw')]);
        $query->andFilterWhere(['=', 'beneficiaries_bnba_tahap_1.rt', Arr::get($params, 'rt')]);
        $query->andFilterWhere(['like', 'notes_reason', Arr::get($params, 'notes_reason')]);

        return $this->getQueryAll($query, $params);
    }

    protected function getQueryAll($query, $params)
    {
        $pageLimit = Arr::get($params, 'limit');
        $sortBy    = Arr::get($params, 'sort_by', 'id');
        $sortOrder = Arr::get($params, 'sort_order', 'descending');
        $sortOrder = ModelHelper::getSortOrder($sortOrder);

        $defaultOrder = [ $sortBy => $sortOrder ];

        return new ActiveDataProvider([
            'query'      => $query,
            'sort'       => [
                'defaultOrder' => $defaultOrder,
                'attributes' => [
                    'id',
                    'nik',
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
