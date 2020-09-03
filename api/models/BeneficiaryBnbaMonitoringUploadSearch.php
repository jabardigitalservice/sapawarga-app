<?php

namespace app\models;

use app\components\ModelHelper;
use Illuminate\Support\Arr;
use yii\data\ActiveDataProvider;

/**
 * BeneficiarySearch represents the model behind the search form of `app\models\BeneficiaryBnbaMonitoringUpload`.
 */
class BeneficiaryBnbaMonitoringUploadSearch extends BeneficiaryBnbaMonitoringUpload
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
        $query = BeneficiaryBnbaMonitoringUpload::find();

        // Filtering
        $query->andFilterWhere(['id' => $this->id]);
        $query->andFilterWhere(['code_bps' => Arr::get($params, 'code_bps')]);
        $query->andFilterWhere(['kabkota_name' => Arr::get($params, 'kabkota_name')]);
        $query->andFilterWhere(['tahap_bantuan' => Arr::get($params, 'tahap_bantuan')]);
        $query->andFilterWhere(['is_dtks' => Arr::get($params, 'is_dtks')]);

        return $this->getQueryAll($query, $params);
    }

    protected function getQueryAll($query, $params)
    {
        $pageLimit = Arr::get($params, 'limit');
        $sortBy = Arr::get($params, 'sort_by', 'code_bps');
        $sortOrder = Arr::get($params, 'sort_order', 'ascending');
        $sortOrder = ModelHelper::getSortOrder($sortOrder);

        return new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => $pageLimit,
            ],
        ]);
    }
}
