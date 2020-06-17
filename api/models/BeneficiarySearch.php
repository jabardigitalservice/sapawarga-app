<?php

namespace app\models;

use app\components\ModelHelper;
use app\models\beneficiary\BeneficiaryApproval;
use Illuminate\Support\Arr;
use yii\data\ActiveDataProvider;

/**
 * BeneficiarySearch represents the model behind the search form of `app\models\Beneficiary`.
 */
class BeneficiarySearch extends Beneficiary
{
    const SCENARIO_LIST_USER = 'list-user';
    const SCENARIO_LIST_APPROVAL = 'list-approval';

    public $userRole;
    public $tahap;

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $attributes = ['tahap'];

        $scenarios[self::SCENARIO_LIST_USER] = $attributes;
        $scenarios[self::SCENARIO_LIST_APPROVAL] = $attributes;
        return $scenarios;
    }

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
     * @param array $paramsp
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Beneficiary::find()->where(['=', 'status', Beneficiary::STATUS_ACTIVE]);

        // Filtering
        $query->andFilterWhere(['id' => $this->id]);
        $query->andFilterWhere(['like', 'name', Arr::get($params, 'name')]);
        $query->andFilterWhere(['nik' => Arr::get($params, 'nik')]);
        $query->andFilterWhere(['kabkota_id' => Arr::get($params, 'kabkota_id')]);
        $query->andFilterWhere(['kec_id' => Arr::get($params, 'kec_id')]);
        $query->andFilterWhere(['kel_id' => Arr::get($params, 'kel_id')]);
        $query->andFilterWhere(['rw' => ltrim(Arr::get($params, 'rw'), '0')]);
        $query->andFilterWhere(['rt' => ltrim(Arr::get($params, 'rt'), '0')]);
        $query->andFilterWhere(['like', 'rw', Arr::get($params, 'rw_like')]);
        $query->andFilterWhere(['like', 'rt', Arr::get($params, 'rt_like')]);

        $query->andFilterWhere(['domicile_kabkota_bps_id' => Arr::get($params, 'domicile_kabkota_bps_id')]);
        $query->andFilterWhere(['domicile_kec_bps_id' => Arr::get($params, 'domicile_kec_bps_id')]);
        $query->andFilterWhere(['domicile_kel_bps_id' => Arr::get($params, 'domicile_kel_bps_id')]);
        $query->andFilterWhere(['domicile_rt' => ltrim(Arr::get($params, 'domicile_rt'), '0')]);
        $query->andFilterWhere(['domicile_rw' => ltrim(Arr::get($params, 'domicile_rw'), '0')]);
        $query->andFilterWhere(['like', 'domicile_rt', Arr::get($params, 'domicile_rt_like')]);
        $query->andFilterWhere(['like', 'domicile_rw', Arr::get($params, 'domicile_rw_like')]);

        // Handle status_verification filtering based on scenario
        if ($this->scenario === self::SCENARIO_LIST_USER) {
            $this->getQueryListUser($query, $params);
        } elseif ($this->scenario === self::SCENARIO_LIST_APPROVAL) {
            $this->getQueryListApproval($query, $params);
        }

        $query->andFilterWhere(['status' => Arr::get($params, 'status')]);

        // Use different column for each tahap (tahap 1 until tahap 4)
        if ($this->tahap) {
            $query->andWhere(['is not', "tahap_{$this->tahap}_verval", null]);
        }

        return $this->getQueryAll($query, $params);
    }

    protected function getQueryListUser($query, $params)
    {
        // Includes verified data that have been followed up to desa/kel/kec/kab/kota for approval (status_verification >= Beneficiary::STATUS_VERIFIED),
        if (Arr::get($params, 'status_verification') < Beneficiary::STATUS_VERIFIED) {
            $query->andFilterWhere(['status_verification' => Arr::get($params, 'status_verification')]);
        } else {
            $query->andFilterWhere(['>=', 'status_verification', Arr::get($params, 'status_verification')]);
        }
    }

    protected function getQueryListApproval($query, $params)
    {
        $type = Arr::get($params, 'type');
        $statusVerificationFilter = Arr::get($params, 'status_verification');
        $statuses = BeneficiaryApproval::APPROVAL_MAP[$type];

        // different filter behavior based on `status_verification` filter
        if (!$statusVerificationFilter) {
            $query->andFilterWhere(['>=', 'status_verification', $statuses['pending']]);
        } elseif ($statusVerificationFilter == $statuses['approved']) {
            $query->andFilterWhere(['>=', 'status_verification', $statusVerificationFilter]);
        } else {
            $query->andFilterWhere(['status_verification' => $statusVerificationFilter]);
        }
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
                'defaultOrder' => $defaultOrder,
                'attributes' => [
                    'name',
                    'nik',
                    'rt',
                    'rw',
                    'domicile_rt',
                    'domicile_rw',
                    'income_before',
                    'income_after',
                    'status_verification',
                    'total_family_members',
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
