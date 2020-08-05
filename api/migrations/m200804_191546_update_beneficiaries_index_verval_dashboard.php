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
            'idx-beneficiaries-dashboard-tahap-all',
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
            'idx-beneficiaries-dashboard-tahap-1',
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
            'idx-beneficiaries-dashboard-tahap-2',
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
            'idx-beneficiaries-dashboard-tahap-3',
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
            'idx-beneficiaries-dashboard-tahap-4',
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
        $this->dropIndex('idx-beneficiaries-dashboard-tahap-4', 'beneficiaries');
        $this->dropIndex('idx-beneficiaries-dashboard-tahap-3', 'beneficiaries');
        $this->dropIndex('idx-beneficiaries-dashboard-tahap-2', 'beneficiaries');
        $this->dropIndex('idx-beneficiaries-dashboard-tahap-1', 'beneficiaries');
        $this->dropIndex('idx-beneficiaries-dashboard-tahap-all', 'beneficiaries');

        $this->createIndex(
            'idx-beneficiaries-dashboard-tahap-all-provinsi',
            'beneficiaries',
            ['domicile_kabkota_bps_id', 'status_verification', 'created_by']
        );

        $this->createIndex(
            'idx-beneficiaries-dashboard-tahap-1-provinsi',
            'beneficiaries',
            ['domicile_kabkota_bps_id', 'tahap_1_verval', 'created_by']
        );

        $this->createIndex(
            'idx-beneficiaries-dashboard-tahap-2-provinsi',
            'beneficiaries',
            ['domicile_kabkota_bps_id', 'tahap_2_verval', 'created_by']
        );

        $this->createIndex(
            'idx-beneficiaries-dashboard-tahap-3-provinsi',
            'beneficiaries',
            ['domicile_kabkota_bps_id', 'tahap_3_verval', 'created_by']
        );

        $this->createIndex(
            'idx-beneficiaries-dashboard-tahap-4-provinsi',
            'beneficiaries',
            ['domicile_kabkota_bps_id', 'tahap_4_verval', 'created_by']
        );
    }
}
