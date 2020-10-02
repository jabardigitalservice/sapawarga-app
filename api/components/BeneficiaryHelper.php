<?php

namespace app\components;

use Yii;
use app\models\Beneficiary;
use app\models\BeneficiaryBnbaTahapSatu;

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

    /**
     * Masking NIK
     *
     * @param integer $nik
     * @return string
     */
    public static function getNikMasking($nik)
    {
        if (empty($nik)) {
            return $nik;
        }

        // Show the 10 first characters, mask the remainder
        $maskMultiplier = max((strlen($nik) - 10), 0);
        $maskedNIK = substr($nik, 0, 10) . str_repeat('*', $maskMultiplier);
        // Add separator every 4 characters
        return implode('-', str_split($maskedNIK, 4));
    }

    /**
     * Masking KK (Kartu Keluarga)
     *
     * @param integer $noKk
     * @return string
     */
    public static function getKkMasking($noKk)
    {
        if (empty($noKk)) {
            return $noKk;
        }

        // Show the 10 first characters, mask the remainder
        $maskMultiplier = max((strlen($noKk) - 10), 0);
        $maskedKk = substr($noKk, 0, 10) . str_repeat('*', $maskMultiplier);

        return $maskedKk;
    }

    /**
     * Masking name
     *
     * @param integer $name
     * @return string
     */
    public static function getNameMasking($name)
    {
        if (strlen($name) <= 1) {
            return $name;
        }

        $explodeWords = explode(' ', $name);

        $nameMasking = '';
        foreach ($explodeWords as $key => $word) {
            if (strlen($word) <= 3) {
                $nameMasking .= $word . ' ';
            } else {
                $nameMasking .= substr($word, 0, 4) . str_repeat('*', strlen($word) - 4) . ' ';
            }
        }

        return rtrim($nameMasking);
    }

    /**
     * Masking address
     *
     * @param integer $address
     * @return string
     */
    public static function getAddressMasking($address)
    {
        if (str_word_count($address) <= 3) {
            return preg_replace('/[0-9]+/', '', $address);
        }

        $someWords = implode(' ', array_slice(explode(' ', $address), 0, 3));
        $explodeWords = explode(' ', $someWords);

        $addressMasking = '';
        foreach ($explodeWords as $key => $word) {
            $addressMasking .= preg_replace('/[0-9]+/', '', $word) . ' ';
        }

        return rtrim($addressMasking);
    }

    /**
     * Get Bansos Type List and Detail
     *
     * @param integer $bansosTypeId
     * @return array / string
     */
    public static function getBansosTypeList($bansosTypeId = null)
    {
        $bansosType = [
            BeneficiaryBnbaTahapSatu::TYPE_PKH => Yii::t('app', 'type.beneficiaries.pkh'),
            BeneficiaryBnbaTahapSatu::TYPE_BNPT => Yii::t('app', 'type.beneficiaries.bnpt'),
            BeneficiaryBnbaTahapSatu::TYPE_BANSOS => Yii::t('app', 'type.beneficiaries.bnpt_perluasan'),
            BeneficiaryBnbaTahapSatu::TYPE_BANSOS_TUNAI => Yii::t('app', 'type.beneficiaries.bansos_tunai'),
            BeneficiaryBnbaTahapSatu::TYPE_BANSOS_PRESIDEN_SEMBAKO => Yii::t('app', 'type.beneficiaries.bansos_presiden_sembako'),
            BeneficiaryBnbaTahapSatu::TYPE_BANSOS_PROVINSI => Yii::t('app', 'type.beneficiaries.bansos_provinsi'),
            BeneficiaryBnbaTahapSatu::TYPE_DANA_DESA => Yii::t('app', 'type.beneficiaries.dana_desa'),
            BeneficiaryBnbaTahapSatu::TYPE_BANSOS_KABKOTA => Yii::t('app', 'type.beneficiaries.bansos_kabkota'),
        ];

        if ($bansosTypeId != null) {
            $bansosType = $bansosType[$bansosTypeId] ?? '';
        }

        return $bansosType;
    }
}
