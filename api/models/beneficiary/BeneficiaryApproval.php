<?php

namespace app\models\beneficiary;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use app\models\Area;
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
        $area = Area::find()->where(['id' => Arr::get($params, 'area_id')])->one();
        $code_bps = $area->code_bps ?? null;

        $statutes = [
            Beneficiary::STATUS_VERIFIED,
            Beneficiary::STATUS_PENDING,
            Beneficiary::STATUS_REJECT,
        ];

        $counts = Beneficiary::find()
            ->select(['status_verification','COUNT(status) AS jumlah'])
            // ->where(['=','domicile_kabkota_bps_id', $code_bps])
            ->where(['in', 'status_verification', $statutes])
            ->groupBy(['status_verification'])
            ->asArray()
            ->all();
        $counts = new Collection($counts);
        $counts = $this->transformCount($counts);

        return $counts;
    }

    public function transformCount($lists)
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
        $jml = Arr::pluck($lists, 'jumlah', 'status_verification');
        $total = 0;
        foreach ($status_maps as $key => $map) {
            $data[$map] = isset($jml[$key]) ? intval($jml[$key]) : 0;
            $total += $data[$map];
        }
        $data['total'] = $total;
        return $data;
    }
}
