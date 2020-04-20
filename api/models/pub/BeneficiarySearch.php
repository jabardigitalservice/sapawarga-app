<?php

namespace app\models\pub;

use app\components\ModelHelper;
use Illuminate\Support\Arr;
use yii\data\ActiveDataProvider;
use yii\data\SqlDataProvider;

/**
 * BeneficiarySearch represents the model behind the search form of `app\models\pub\Beneficiary`.
 */
class BeneficiarySearch extends Beneficiary
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
        $query = Beneficiary::find()->where(['=', 'status', Beneficiary::STATUS_ACTIVE]);

        // Filtering
        $query->andFilterWhere(['id' => $this->id]);
        $query->andFilterWhere(['like', 'name', Arr::get($params, 'name')]);
        $query->andFilterWhere(['status_verification' => Arr::get($params, 'status_verification')]);
        $query->andFilterWhere(['nik' => Arr::get($params, 'nik')]);
        $query->andFilterWhere(['kabkota_id' => Arr::get($params, 'kabkota_id')]);
        $query->andFilterWhere(['kec_id' => Arr::get($params, 'kec_id')]);
        $query->andFilterWhere(['kel_id' => Arr::get($params, 'kel_id')]);
        $query->andFilterWhere(['rw' => Arr::get($params, 'rw')]);
        $query->andFilterWhere(['rt' => Arr::get($params, 'rt')]);

        return $this->getQueryAll($query, $params);
    }

    /**
     * Creates data provider instance applied for get total beneficiaries group by status verification
     *
     * @param array
     * $params['kabkota_id'] Filtering by kabkota_id
     * @return SqlDataProvider
     */
    public function getSummaryStatusVerification($params)
    {
        $conditional = '';
        $paramsSql = [':status' => Beneficiary::STATUS_ACTIVE];

        // Filtering
        if (Arr::get($params, 'kabkota_id')) {
            $conditional .= 'AND kabkota_id = :kabkota_id ';
            $paramsSql[':kabkota_id'] = Arr::get($params, 'kabkota_id');
        }

        $sql = "SELECT status_verification, count(status_verification) AS total FROM beneficiaries WHERE status = :status $conditional GROUP BY status_verification";

        $provider =  new SqlDataProvider([
            'sql' => $sql,
            'params' => $paramsSql,
        ]);

        $data = ['PENDING' => 0, 'REJECT' => 0, 'APPROVED' => 0];
        foreach ($data as $key => $value) {
            foreach ($provider->getModels() as $val) {
                if ($val['status_verification'] == 1) {
                    $data['PENDING'] = $val['total'];
                } elseif ($val['status_verification'] == 2) {
                    $data['REJECT'] = $val['total'];
                } elseif ($val['status_verification'] == 3) {
                    $data['APPROVED'] = $val['total'];
                }
            }
        }

        return $data;
    }

    protected function getQueryAll($query, $params)
    {
        $pageLimit = Arr::get($params, 'limit');
        $sortBy    = Arr::get($params, 'sort_by', 'name');
        $sortOrder = Arr::get($params, 'sort_order', 'ascending');
        $sortOrder = ModelHelper::getSortOrder($sortOrder);

        return new ActiveDataProvider([
            'query'      => $query,
            'sort'       => [
                'defaultOrder' => [$sortBy => $sortOrder],
                'attributes' => [
                    'name',
                    'rt',
                    'rw',
                    'status_verification',
                ],
            ],
            'pagination' => [
                'pageSize' => $pageLimit,
            ],
        ]);
    }
}
