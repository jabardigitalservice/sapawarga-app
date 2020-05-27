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
    const APPROVAL_MAP = [
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
     * @return BeneficiaryApproval
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

        // select required status_verification values based on $type
        $statusApproved = self::APPROVAL_MAP[$type]['approved'];
        $statusRejected = self::APPROVAL_MAP[$type]['rejected'];
        $statusPending = self::APPROVAL_MAP[$type]['pending'];

        $counts = Beneficiary::find()->select([
            "SUM(status_verification >= ${statusApproved}) as 'approved'",
            "SUM(status_verification = ${statusRejected}) as 'rejected'",
            "SUM(status_verification = ${statusPending}) as 'pending'",
        ]);
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
        $counts = $counts->asArray()->all();

        // instantiate the model as return value
        $model = new BeneficiaryApproval();
        $model->approved = intval($counts[0]['approved']);
        $model->rejected = intval($counts[0]['rejected']);
        $model->pending = intval($counts[0]['pending']);
        $model->total = $model->approved + $model->rejected + $model->pending;
        return $model;
    }
}
