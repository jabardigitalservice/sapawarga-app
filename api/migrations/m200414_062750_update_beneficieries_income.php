<?php

use app\components\CustomMigration;

/**
 * Class m200414_062750_update_beneficieries_income */
class m200414_062750_update_beneficieries_income extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // make defautl value
        $this->alterColumn('beneficiaries', 'status_verification', $this->integer()->defaultValue(1));
        $this->alterColumn('beneficiaries', 'income_after', $this->integer()->defaultValue(0));
        $this->alterColumn('beneficiaries', 'income_before', $this->integer()->defaultValue(0));

        // Add province
        $this->addColumn('beneficiaries', 'province_id', $this->integer()->after('kel_bps_id'));

        // Domicile
        $this->addColumn('beneficiaries', 'domicile_address', $this->string()->after('rw'));
        $this->addColumn('beneficiaries', 'domicile_rw', $this->string()->after('rw'));
        $this->addColumn('beneficiaries', 'domicile_rt', $this->string()->after('rw'));
        $this->addColumn('beneficiaries', 'domicile_kel_bps_id', $this->integer()->after('rw'));
        $this->addColumn('beneficiaries', 'domicile_kec_bps_id', $this->integer()->after('rw'));
        $this->addColumn('beneficiaries', 'domicile_kabkota_bps_id', $this->integer()->after('rw'));
        $this->addColumn('beneficiaries', 'domicile_province_bps_id', $this->integer()->after('rw'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('beneficiaries', 'status_verification', $this->integer());
        $this->alterColumn('beneficiaries', 'income_after', $this->integer());
        $this->alterColumn('beneficiaries', 'income_before', $this->integer());

        $this->dropColumn('beneficiaries', 'province_id');

        $this->dropColumn('beneficiaries', 'domicile_province_bps_id');
        $this->dropColumn('beneficiaries', 'domicile_kabkota_bps_id');
        $this->dropColumn('beneficiaries', 'domicile_kec_bps_id');
        $this->dropColumn('beneficiaries', 'domicile_kel_bps_id');
        $this->dropColumn('beneficiaries', 'domicile_rt');
        $this->dropColumn('beneficiaries', 'domicile_rw');
        $this->dropColumn('beneficiaries', 'domicile_address');
    }
}
