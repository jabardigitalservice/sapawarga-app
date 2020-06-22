<?php

namespace app\components;

use Yii;
use app\models\Beneficiary;

class BeneficiaryHelper
{
    /**
     * Returns current tahap for both verval and BNBA
     * @return array
     */
    public static function getCurrentTahap()
    {
        $data = (new \yii\db\Query())
            ->from('beneficiaries_current_tahap')
            ->all();

        if (count($data) <= 0) {
            return null;
        }

        unset($data[0]['id']);
        return $data[0];
    }

    /**
     * Determines column to be used as status_verification, depending on $tahap paramter value
     * Possible values: status_verification, tahap_1_verval, tahap_2_verval, tahap_3_verval, tahap_4_verval
     *
     * @param integer $tahap
     * @return string
     */
    public static function getStatusVerificationColumn($tahap)
    {
        $result = 'status_verification';
        if ($tahap) {
            $result = "tahap_{$tahap}_verval";
        }
        return $result;
    }
}
