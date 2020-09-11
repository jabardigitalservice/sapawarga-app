<?php

use app\components\CustomMigration;

/**
 * Class m200824_075749_update_location_names_beneficieries */
class m200824_075749_update_location_names_beneficieries extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Add location names
        $this->addColumn('beneficiaries', 'domicile_kel_name', $this->string()->after('domicile_kel_bps_id')->defaultValue(null));
        $this->addColumn('beneficiaries', 'domicile_kec_name', $this->string()->after('domicile_kel_bps_id')->defaultValue(null));
        $this->addColumn('beneficiaries', 'domicile_kabkota_name', $this->string()->after('domicile_kel_bps_id')->defaultValue(null));

        // Delete unused column
        $this->dropColumn('beneficiaries', 'province_id');
        $this->dropColumn('beneficiaries', 'kabkota_id');
        $this->dropColumn('beneficiaries', 'kec_id');
        $this->dropColumn('beneficiaries', 'kel_id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('beneficiaries', 'domicile_kabkota_name');
        $this->dropColumn('beneficiaries', 'domicile_kec_name');
        $this->dropColumn('beneficiaries', 'domicile_kel_name');

        $this->addColumn('beneficiaries', 'kel_id', $this->integer()->after('kel_bps_id')->defaultValue(null));
        $this->addColumn('beneficiaries', 'kec_id', $this->integer()->after('kel_bps_id')->defaultValue(null));
        $this->addColumn('beneficiaries', 'kabkota_id', $this->integer()->after('kel_bps_id')->defaultValue(null));
        $this->addColumn('beneficiaries', 'province_id', $this->integer()->after('kel_bps_id')->defaultValue(null));
    }
}
