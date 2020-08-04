<?php

use app\components\CustomMigration;

/**
 * Class m200804_191546_update */
class m200804_191546_update extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropIndex('idx-beneficiaries-dashboard-tahap-all-provinsi', 'beneficiaries');
        $this->dropIndex('idx-beneficiaries-dashboard-tahap-1-provinsi', 'beneficiaries');
        $this->dropIndex('idx-beneficiaries-dashboard-tahap-2-provinsi', 'beneficiaries');
        $this->dropIndex('idx-beneficiaries-dashboard-tahap-3-provinsi', 'beneficiaries');
        $this->dropIndex('idx-beneficiaries-dashboard-tahap-4-provinsi', 'beneficiaries');

        $this->createIndex(
            'idx-beneficiaries-dashboard-tahap-all-provinsi',
            'beneficiaries',
            [
                'status',
                'domicile_kabkota_bps_id',
                'domicile_kec_bps_id',
                'domicile_kel_bps_id',
                'domicile_rw',
                'created_by',
                'status_verification',
            ]
        );

        $this->createIndex(
            'idx-beneficiaries-dashboard-tahap-1-provinsi',
            'beneficiaries',
            [
                'status',
                'domicile_kabkota_bps_id',
                'domicile_kec_bps_id',
                'domicile_kel_bps_id',
                'domicile_rw',
                'created_by',
                'tahap_1_verval',
            ]
        );

        $this->createIndex(
            'idx-beneficiaries-dashboard-tahap-2-provinsi',
            'beneficiaries',
            [
                'status',
                'domicile_kabkota_bps_id',
                'domicile_kec_bps_id',
                'domicile_kel_bps_id',
                'domicile_rw',
                'created_by',
                'tahap_2_verval',
            ]
        );

        $this->createIndex(
            'idx-beneficiaries-dashboard-tahap-3-provinsi',
            'beneficiaries',
            [
                'status',
                'domicile_kabkota_bps_id',
                'domicile_kec_bps_id',
                'domicile_kel_bps_id',
                'domicile_rw',
                'created_by',
                'tahap_3_verval',
            ]
        );

        $this->createIndex(
            'idx-beneficiaries-dashboard-tahap-4-provinsi',
            'beneficiaries',
            [
                'status',
                'domicile_kabkota_bps_id',
                'domicile_kec_bps_id',
                'domicile_kel_bps_id',
                'domicile_rw',
                'created_by',
                'tahap_4_verval',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m200804_191546_update cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200804_191546_update cannot be reverted.\n";

        return false;
    }
    */
}
