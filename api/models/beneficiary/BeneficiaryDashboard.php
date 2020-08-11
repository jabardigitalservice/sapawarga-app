<?php

namespace app\models\beneficiary;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use app\components\BeneficiaryHelper;
use app\models\Area;
use app\models\Beneficiary;
use Yii;

/**
 * BeneficiaryDashboard represents the model behind functionalities related to Bansos Dashboard on webadmin
 *
 */
class BeneficiaryDashboard extends Beneficiary
{
    public $tahap;
    public $statusVerificationColumn = 'status_verification';

    /**
     * Transforms result from database query into specific data structure, either for Dashboard Sumamary or Dashboard List
     *
     * @param Illuminate\Support\Collection $lists array that needs to be transformed
     * @param string $statusVerificationColumn column to be used as status_verification, depending on 'tahap' parameter value
     *
     * @return array
     */
    protected function transformCount($lists, $statusVerificationColumn)
    {
        $status_maps = [
            '1' => 'pending',
            '2' => 'rejected',
            '3' => 'approved',
            '4' => 'rejected_kel',
            '5' => 'approved_kel',
            '6' => 'rejected_kec',
            '7' => 'approved_kec',
            '8' => 'rejected_kabkota',
            '9' => 'approved_kabkota',
        ];
        $data = [];
        $jml = $lists->pluck('jumlah', $statusVerificationColumn);
        $total = 0;
        foreach ($status_maps as $key => $map) {
            $data[$map] = isset($jml[$key]) ? intval($jml[$key]) : 0;
            $total += $data[$map];
        }
        $data['total'] = $total;
        return $data;
    }

    /* VERVAL DASHBOARD - SUMMARY */

    /**
     * Returns database query result for Dashboard Summary.
     *
     * @param array $conditionals additional 'where' statements to filter data by BPS code
     *
     * @return array
     */
    protected function getDashboardSummaryQuery($conditionals)
    {
        $params = Yii::$app->request->getQueryParams();
        $statusVerificationColumn = BeneficiaryHelper::getStatusVerificationColumn(Arr::get($params, 'tahap'));

        $query = (new \yii\db\Query())
            ->select([$statusVerificationColumn, 'COUNT(*) AS jumlah'])
            ->from('beneficiaries')
            ->where(['=', 'status', Beneficiary::STATUS_ACTIVE]);
        foreach ($conditionals as $conditional) {
            $query = $query->andWhere($conditional);
        }
        $query = $query->groupBy([$statusVerificationColumn])
            ->createCommand()
            ->queryAll();

        return $query;
    }

    /**
     * Returns data for Dashboard Summary.
     *
     * @param array $conditionals additional 'where' statements to filter data by BPS code
     *
     * @return array
     */
    protected function getDashboardSummaryData($conditionals)
    {
        $params = Yii::$app->request->getQueryParams();
        $statusVerificationColumn = BeneficiaryHelper::getStatusVerificationColumn(Arr::get($params, 'tahap'));

        $counts = $this->getDashboardSummaryQuery($conditionals);
        $counts = new Collection($counts);
        $counts = $this->transformCount($counts, $statusVerificationColumn);

        return $counts;
    }

    /**
     * Returns data for Dashboard Summary.
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
        $type = Arr::get($params, 'type');
        $code_bps = Arr::get($params, 'code_bps');
        $rw = Arr::get($params, 'rw');

        switch ($type) {
            case 'provinsi':
                $counts = $this->getDashboardSummaryData([]);
                $counts_baru = $this->getDashboardSummaryData([['<>', 'created_by', 2]]);
                break;
            case 'kabkota':
                $counts = $this->getDashboardSummaryData([['=', 'domicile_kabkota_bps_id', $code_bps]]);
                $counts_baru = $this->getDashboardSummaryData([
                    ['=', 'domicile_kabkota_bps_id', $code_bps],
                    ['<>', 'created_by', 2],
                ]);
                break;
            case 'kec':
                $counts = $this->getDashboardSummaryData([
                    ['=', 'domicile_kabkota_bps_id', substr($code_bps, 0, 4)],
                    ['=', 'domicile_kec_bps_id', $code_bps],
                ]);
                $counts_baru = $this->getDashboardSummaryData([
                    ['=', 'domicile_kabkota_bps_id', substr($code_bps, 0, 4)],
                    ['=', 'domicile_kec_bps_id', $code_bps],
                    ['<>', 'created_by', 2],
                ]);
                break;
            case 'kel':
                $counts = $this->getDashboardSummaryData([
                    ['=', 'domicile_kabkota_bps_id', substr($code_bps, 0, 4)],
                    ['=', 'domicile_kec_bps_id', substr($code_bps, 0, 7)],
                    ['=', 'domicile_kel_bps_id', $code_bps],
                ]);
                $counts_baru = $this->getDashboardSummaryData([
                    ['=', 'domicile_kabkota_bps_id', substr($code_bps, 0, 4)],
                    ['=', 'domicile_kec_bps_id', substr($code_bps, 0, 7)],
                    ['=', 'domicile_kel_bps_id', $code_bps],
                    ['<>', 'created_by', 2],
                ]);
                break;
            case 'rw':
                $counts = $this->getDashboardSummaryData([
                    ['=', 'domicile_kabkota_bps_id', substr($code_bps, 0, 4)],
                    ['=', 'domicile_kec_bps_id', substr($code_bps, 0, 7)],
                    ['=', 'domicile_kel_bps_id', $code_bps],
                    ['=', 'domicile_rw', $rw],
                ]);
                $counts_baru = $this->getDashboardSummaryData([
                    ['=', 'domicile_kabkota_bps_id', substr($code_bps, 0, 4)],
                    ['=', 'domicile_kec_bps_id', substr($code_bps, 0, 7)],
                    ['=', 'domicile_kel_bps_id', $code_bps],
                    ['=', 'domicile_rw', $rw],
                    ['<>', 'created_by', 2],
                ]);
                break;
        }
        $counts['baru'] = $counts_baru;
        return $counts;
    }

    /* VERVAL DASHBOARD - LIST */

    /**
     * Returns data for Dashboard List.
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
