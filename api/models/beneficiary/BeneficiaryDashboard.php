<?php

namespace app\models\beneficiary;

use Illuminate\Support\Arr;
use app\components\BeneficiaryHelper;
use app\models\Area;
use app\models\Beneficiary;

/**
 * BeneficiaryDashboard represents the model behind functionalities related to Bansos Dashboard on webadmin
 *
 */
class BeneficiaryDashboard extends Beneficiary
{
    public $tahap;
    public $statusVerificationColumn = 'status_verification';

    /**
     * Returns data for Beneficiary Dashboard, Summary/Statistic part.
     *
     * @param array $params['type'] type of dashboard (provinsi | kabkota | kec | kel)
     * @param array $params['code_bps'] BPS code of the data
     * @param array $params['rw'] RW of the data (optional, applies only to 'rw' type)
     * @param array $params['tahap'] number of tahap (null | 1..4)
     *
     * @return BeneficiaryDashboard
     */
    public function getDashboardSummary($params)
    {
        return 'ok';
    }

    /**
     * Returns data for Beneficiary Dashboard, List part.
     *
     * @param array $params['type'] type of dashboard (provinsi | kabkota | kec | kel)
     * @param array $params['code_bps'] BPS code of the data
     * @param array $params['rw'] RW of the data (optional, applies only to 'rw' type)
     * @param array $params['tahap'] number of tahap (null | 1..4)
     *
     * @return BeneficiaryDashboard
     */
    public function getDashboardList($params)
    {
        return 'ok';
    }
}
