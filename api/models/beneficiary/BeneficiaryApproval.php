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
            'pending' => Beneficiary::STATUS_APPROVED_KEC,
            'rejected' => Beneficiary::STATUS_REJECTED_KABKOTA,
            'approved' => Beneficiary::STATUS_APPROVED_KABKOTA,
        ],
        Beneficiary::TYPE_KABKOTA => [
            'pending' => Beneficiary::STATUS_APPROVED_KEC,
            'rejected' => Beneficiary::STATUS_REJECTED_KABKOTA,
            'approved' => Beneficiary::STATUS_APPROVED_KABKOTA,
        ],
        Beneficiary::TYPE_KEC => [
            'pending' => Beneficiary::STATUS_APPROVED_KEL,
            'rejected' => Beneficiary::STATUS_REJECTED_KEC,
            'approved' => Beneficiary::STATUS_APPROVED_KEC,
        ],
        Beneficiary::TYPE_KEL => [
            'pending' => Beneficiary::STATUS_VERIFIED,
            'rejected' => Beneficiary::STATUS_REJECTED_KEL,
            'approved' => Beneficiary::STATUS_APPROVED_KEL,
        ],
    ];

    public $approved;
    public $rejected;
    public $pending;
    public $total;

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
        $statuses = array_values(self::DASHBOARD_STATUSES[$type]);

        // select required status_verification based on $statuses value
        $counts = Beneficiary::find()->select(['status_verification','COUNT(status) AS jumlah']);
        if ($area_id) {
            switch ($type) {
                case Beneficiary::TYPE_KABKOTA:
                    $counts = $counts->andWhere(['domicile_kabkota_bps_id' => $area_id]);
                    break;
                case Beneficiary::TYPE_KEC:
                    $counts = $counts->andWhere(['domicile_kec_bps_id' => $area_id]);
                    break;
                case Beneficiary::TYPE_KEL:
                    $counts = $counts->andWhere(['domicile_kel_bps_id' => $area_id]);
                    break;
            }
        }
        $counts = $counts->andWhere(['in', 'status_verification', $statuses])
            ->groupBy(['status_verification'])
            ->asArray()
            ->all();

        // instantiate the model as return value
        $model = new BeneficiaryApproval();
        $idx = array_search(self::DASHBOARD_STATUSES[$type]['approved'], array_column($counts, 'status_verification'));
        $model->approved = $idx !== false ? intval($counts[$idx]['jumlah']) : 0;
        $idx = array_search(self::DASHBOARD_STATUSES[$type]['rejected'], array_column($counts, 'status_verification'));
        $model->rejected = $idx !== false ? intval($counts[$idx]['jumlah']) : 0;
        $idx = array_search(self::DASHBOARD_STATUSES[$type]['pending'], array_column($counts, 'status_verification'));
        $model->pending = $idx !== false ? intval($counts[$idx]['jumlah']) : 0;
        $model->total = $model->approved + $model->rejected + $model->pending;
        return $model;
    }
}
