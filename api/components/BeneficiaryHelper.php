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

}
