<?php

namespace app\models\pub;

use app\components\BeneficiaryHelper;
use app\components\ModelHelper;
use Illuminate\Support\Arr;
use yii\data\ActiveDataProvider;
use yii\data\SqlDataProvider;

/**
 * BeneficiarySearch represents the model behind the search form of `app\models\pub\Beneficiary`.
 */
class BeneficiarySearch extends Beneficiary
{
    public $tahap;
    public $statusVerificationColumn = 'status_verification';

    public function rules()
    {
        return [
            ['tahap', 'integer'],
            ['tahap', 'in', 'range' => [1, 2, 3, 4]],
        ];
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $this->statusVerificationColumn = BeneficiaryHelper::getStatusVerificationColumn($this->tahap);

        $query = Beneficiary::find()->where(['=', 'status', Beneficiary::STATUS_ACTIVE]);

        // Filtering
        $query->andFilterWhere(['id' => $this->id]);
        $query->andFilterWhere(['like', 'name', Arr::get($params, 'name') . '%', false]);

        $query->andFilterWhere(['domicile_kabkota_bps_id' => Arr::get($params, 'domicile_kabkota_bps_id')]);
        $query->andFilterWhere(['domicile_kec_bps_id' => Arr::get($params, 'domicile_kec_bps_id')]);
        $query->andFilterWhere(['domicile_kel_bps_id' => Arr::get($params, 'domicile_kel_bps_id')]);
        $query->andFilterWhere(['domicile_rt' => ltrim(Arr::get($params, 'domicile_rt'), '0')]);
        $query->andFilterWhere(['domicile_rw' => ltrim(Arr::get($params, 'domicile_rw'), '0')]);
        $query->andFilterWhere([$this->statusVerificationColumn => Arr::get($params, 'status_verification')]);

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
        $this->statusVerificationColumn = BeneficiaryHelper::getStatusVerificationColumn($this->tahap);

        // Filtering
        if (Arr::get($params, 'domicile_kabkota_bps_id')) {
            $conditional .= 'AND domicile_kabkota_bps_id = :domicile_kabkota_bps_id ';
            $paramsSql[':domicile_kabkota_bps_id'] = Arr::get($params, 'domicile_kabkota_bps_id');
        }
        if (Arr::get($params, 'domicile_kec_bps_id')) {
            $conditional .= 'AND domicile_kec_bps_id = :domicile_kec_bps_id ';
            $paramsSql[':domicile_kec_bps_id'] = Arr::get($params, 'domicile_kec_bps_id');
        }
        if (Arr::get($params, 'domicile_kel_bps_id')) {
            $conditional .= 'AND domicile_kel_bps_id = :domicile_kel_bps_id ';
            $paramsSql[':domicile_kel_bps_id'] = Arr::get($params, 'domicile_kel_bps_id');
        }
        if (Arr::get($params, 'domicile_rw')) {
            $conditional .= 'AND domicile_rw = :domicile_rw ';
            $paramsSql[':domicile_rw'] = Arr::get($params, 'domicile_rw');
        }
        $paramsSql[':status_pending'] = Beneficiary::STATUS_PENDING;
        $paramsSql[':status_reject'] = Beneficiary::STATUS_REJECT;
        $paramsSql[':status_verified'] = Beneficiary::STATUS_VERIFIED;

        $sql = "SELECT SUM({$this->statusVerificationColumn} = :status_pending) AS 'PENDING',
            SUM({$this->statusVerificationColumn} = :status_reject) AS 'REJECT',
            SUM({$this->statusVerificationColumn} >= :status_verified) AS 'APPROVED'
            FROM beneficiaries WHERE status = :status $conditional";

        $provider =  new SqlDataProvider([
            'sql' => $sql,
            'params' => $paramsSql,
        ]);

        $val = $provider->getModels();
        $data = [
            'PENDING' => $val[0]['PENDING'],
            'REJECT' => $val[0]['REJECT'],
            'APPROVED' => $val[0]['APPROVED'],
        ];

        return $data;
    }

    protected function getQueryAll($query, $params)
    {
        // change 'status_verification' sort attribute based on tahap
        $sortAttribute = Arr::get($params, 'sort_by', 'nik');
        if ($sortAttribute == 'status_verification') {
            $sortAttribute = $this->statusVerificationColumn;
        }

        $pageLimit = Arr::get($params, 'limit');
        $sortBy    = $sortAttribute;
        $sortOrder = Arr::get($params, 'sort_order', 'ascending');
        $sortOrder = ModelHelper::getSortOrder($sortOrder);

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'sort'       => [
                'defaultOrder' => [$sortBy => $sortOrder],
                'attributes' => [
                    'nik',
                    'name',
                    'rt',
                    'rw',
                    'status_verification',
                    'tahap_1_verval',
                    'tahap_2_verval',
                    'tahap_3_verval',
                    'tahap_4_verval',
                ],
            ],
            'pagination' => [
                'pageSize' => $pageLimit,
            ],
        ]);

        // Modify status_verification value based on tahap
        if ($this->tahap) {
            $models = $dataProvider->getModels();
            foreach ($models as $model) {
                $model->status_verification = $model[$this->statusVerificationColumn];
            }
            $dataProvider->setModels($models);
        }

        return $dataProvider;
    }
}
