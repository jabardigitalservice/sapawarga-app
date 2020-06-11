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
        $query = BeneficiaryComplain::find()->joinWith('beneficiary')
                    ->where(['=', 'beneficiaries_complain.status', BeneficiaryComplain::STATUS_ACTIVE]);

        // Filtering
        $query->andFilterWhere(['id' => $this->id]);
        $query->andFilterWhere(['like', 'beneficiaries.nik', Arr::get($params, 'nik')]);
        $query->andFilterWhere(['like', 'beneficiaries.name', Arr::get($params, 'name')]);
        $query->andFilterWhere(['like', 'beneficiaries.rt', Arr::get($params, 'rt_like')]);
        $query->andFilterWhere(['like', 'beneficiaries.rw', Arr::get($params, 'rw_like')]);
        $query->andFilterWhere(['like', 'beneficiaries.address', Arr::get($params, 'address')]);
        $query->andFilterWhere(['like', 'beneficiaries.notes_reason', Arr::get($params, 'notes_reason')]);

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
