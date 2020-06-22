<?php

namespace app\components;

use Yii;
use app\models\Beneficiary;

class BeneficiaryHelper
{
    /**
     * Returns current tahap for both verval and BNBA
     *
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
}
