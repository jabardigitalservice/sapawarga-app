<?php

use app\components\CustomMigration;

/**
 * Class m200418_154222_update_beneficiaries_column_type
 * Updates column types of `beneficiaries` table to conform with those from `beneficiaries_raw` table
*/
class m200418_154222_update_beneficiaries_column_type extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('beneficiaries', 'nik', $this->string(100)->notNull());
        $this->alterColumn('beneficiaries', 'no_kk', $this->string(100));
        $this->alterColumn('beneficiaries', 'name', $this->string(100)->notNull());
        $this->alterColumn('beneficiaries', 'province_bps_id', $this->string(10));
        $this->alterColumn('beneficiaries', 'kabkota_bps_id', $this->string(20));
        $this->alterColumn('beneficiaries', 'kec_bps_id', $this->string(20));
        $this->alterColumn('beneficiaries', 'kel_bps_id', $this->string(20));
        $this->alterColumn('beneficiaries', 'domicile_province_bps_id', $this->string(10));
        $this->alterColumn('beneficiaries', 'domicile_kabkota_bps_id', $this->string(20));
        $this->alterColumn('beneficiaries', 'domicile_kec_bps_id', $this->string(20));
        $this->alterColumn('beneficiaries', 'domicile_kel_bps_id', $this->string(20));
        $this->alterColumn('beneficiaries', 'domicile_rt', $this->string(20));
        $this->alterColumn('beneficiaries', 'domicile_rw', $this->string(20));
        $this->alterColumn('beneficiaries', 'domicile_address', $this->string(400));
        $this->alterColumn('beneficiaries', 'phone', $this->string(100));
        $this->alterColumn('beneficiaries', 'income_before', $this->bigInteger()->defaultValue(0));
        $this->alterColumn('beneficiaries', 'income_after', $this->bigInteger()->defaultValue(0));
        $this->alterColumn('beneficiaries', 'notes', $this->text());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('beneficiaries', 'notes', $this->string());
        $this->alterColumn('beneficiaries', 'income_after', $this->integer()->defaultValue(0));
        $this->alterColumn('beneficiaries', 'income_before', $this->integer()->defaultValue(0));
        $this->alterColumn('beneficiaries', 'phone', $this->string());
        $this->alterColumn('beneficiaries', 'domicile_address', $this->string());
        $this->alterColumn('beneficiaries', 'domicile_rw', $this->string());
        $this->alterColumn('beneficiaries', 'domicile_rt', $this->string());
        $this->alterColumn('beneficiaries', 'domicile_kel_bps_id', $this->string());
        $this->alterColumn('beneficiaries', 'domicile_kec_bps_id', $this->string());
        $this->alterColumn('beneficiaries', 'domicile_kabkota_bps_id', $this->string());
        $this->alterColumn('beneficiaries', 'domicile_province_bps_id', $this->string());
        $this->alterColumn('beneficiaries', 'kel_bps_id', $this->string());
        $this->alterColumn('beneficiaries', 'kec_bps_id', $this->string());
        $this->alterColumn('beneficiaries', 'kabkota_bps_id', $this->string());
        $this->alterColumn('beneficiaries', 'province_bps_id', $this->string());
        $this->alterColumn('beneficiaries', 'name', $this->string()->notNull());
        $this->alterColumn('beneficiaries', 'no_kk', $this->string());
        $this->alterColumn('beneficiaries', 'nik', $this->string()->notNull());
    }
}
