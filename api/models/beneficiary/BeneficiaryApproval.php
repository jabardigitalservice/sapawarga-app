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
    // Determines statuses shown for each type of dashboard
    const DASHBOARD_STATUSES = [
        Beneficiary::TYPE_PROVINSI => [
            Beneficiary::STATUS_APPROVED_KEC,
            Beneficiary::STATUS_REJECTED_KABKOTA,
            Beneficiary::STATUS_APPROVED_KABKOTA,
        ],
        Beneficiary::TYPE_KABKOTA => [
            Beneficiary::STATUS_APPROVED_KEC,
            Beneficiary::STATUS_REJECTED_KABKOTA,
            Beneficiary::STATUS_APPROVED_KABKOTA,
        ],
        Beneficiary::TYPE_KEC => [
            Beneficiary::STATUS_APPROVED_KEL,
            Beneficiary::STATUS_REJECTED_KEC,
            Beneficiary::STATUS_APPROVED_KEC,
        ],
        Beneficiary::TYPE_KEL => [
            Beneficiary::STATUS_VERIFIED,
            Beneficiary::STATUS_REJECTED_KEL,
            Beneficiary::STATUS_APPROVED_KEL,
        ],
    ];

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
     * @param array $params['type'] type of dashboard (provinsi | kabkota | kec | kel)
     * @param array $params['area_id'] area id of the user
     *
     * @return array
     */
    public function getDashboardApproval($params)
    {
        // get params, converting area_id to BPS code
        $type = Arr::get($params, 'type');
        $area_id = Arr::get($params, 'area_id');
        if ($area_id) {
            $area = Area::find()->where(['id' => $area_id])->one();
            $area_id = $area->code_bps;
        }
        $statutes = self::DASHBOARD_STATUSES[$type];

        $counts = Beneficiary::find()->select(['status_verification','COUNT(status) AS jumlah']);
        if ($area_id) {
            $counts = $counts->where(['=','domicile_kabkota_bps_id', $area_id]);
        }
        $counts = $counts->where(['in', 'status_verification', $statutes])
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
            if (isset($jml[$key])) {
                $data[$map] = intval($jml[$key]);
                $total += $data[$map];
            }
        }
        $data['total'] = $total;
        return $data;
    }
}
