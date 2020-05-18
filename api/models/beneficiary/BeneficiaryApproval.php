<?php

namespace app\models\beneficiary;

use Illuminate\Support\Arr;
use app\models\Beneficiary;

/**
 * BeneficiaryApproval represents the model behind functionalities related to Bansos Approval
 *
 * @property int $approved
 * @property int $rejected
 * @property int $pending
 * @property int $total
 */
class BeneficiaryApproval extends Beneficiary
{
    public function fields()
    {
        return [
            'approved',
            'rejected',
            'pending',
            'total',
        ];
    }

    /**
     * Returns approval summary based on Beneficiary's status_verification
     *
     * @param array $params['limit'] Limit result data
     * @param array $params['category_id'] Filtering by category_id
     * @param array $params['kabkota_id'] Filtering by kabkota_id
     *
     * @return SqlDataProvider
     */
    public function getDashboardApproval($params)
    {
        // get params
        $type = Arr::get($params, 'type');
        $area_id = Arr::get($params, 'area_id');

        return [];
    }
}
