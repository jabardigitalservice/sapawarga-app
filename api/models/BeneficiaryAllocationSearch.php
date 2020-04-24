<?php

namespace app\models;

use app\components\ModelHelper;
use Illuminate\Support\Arr;
use yii\data\ActiveDataProvider;

/**
 * BeneficiaryAllocationSearch represents the model behind the search form of `app\models\Beneficiary`.
 */
class BeneficiaryAllocationSearch extends Beneficiary
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
        $query = BeneficiaryAllocation::find()->where(['=', 'status', BeneficiaryAllocation::STATUS_ACTIVE]);

        // Filtering
        $query->andFilterWhere(['id' => $this->id]);
        $query->andFilterWhere(['like', 'name', Arr::get($params, 'name')]);
        $query->andFilterWhere(['like', 'nik', Arr::get($params, 'nik')]);
        $query->andFilterWhere(['kabkota_bps_id' => Arr::get($params, 'kabkota_bps_id')]);
        $query->andFilterWhere(['kec_bps_id' => Arr::get($params, 'kec_bps_id')]);
        $query->andFilterWhere(['kel_bps_id' => Arr::get($params, 'kel_bps_id')]);
        $query->andFilterWhere(['rw' => ltrim(Arr::get($params, 'rw'), '0')]);
        $query->andFilterWhere(['rt' => ltrim(Arr::get($params, 'rt'), '0')]);
        $query->andFilterWhere(['status' => Arr::get($params, 'status')]);

        return $this->getQueryAll($query, $params);
    }

    protected function getQueryListUser($query, $params)
    {
        return $this->getQueryAll($query, $params);
    }

    protected function getQueryAll($query, $params)
    {
        $pageLimit = Arr::get($params, 'limit');
        $sortBy    = Arr::get($params, 'sort_by', 'name');
        $sortOrder = Arr::get($params, 'sort_order', 'ascending');
        $sortOrder = ModelHelper::getSortOrder($sortOrder);

        $defaultOrder = [$sortBy => $sortOrder];

        return new ActiveDataProvider([
            'query'      => $query,
            'sort'       => [
                'defaultOrder' => $defaultOrder,
                'attributes' => [
                    'name',
                    'nik',
                    'rt',
                    'rw',
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
