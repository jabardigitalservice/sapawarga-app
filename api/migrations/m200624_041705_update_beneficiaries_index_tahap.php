<?php

use app\components\CustomMigration;

/**
 * Class m200624_041705_update_beneficiaries_index_tahap */
class m200624_041705_update_beneficiaries_index_tahap extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
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

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-beneficiaries-dashboard-tahap-4-provinsi', 'beneficiaries');
        $this->dropIndex('idx-beneficiaries-dashboard-tahap-3-provinsi', 'beneficiaries');
        $this->dropIndex('idx-beneficiaries-dashboard-tahap-2-provinsi', 'beneficiaries');
        $this->dropIndex('idx-beneficiaries-dashboard-tahap-1-provinsi', 'beneficiaries');
        $this->dropIndex('idx-beneficiaries-dashboard-tahap-all-provinsi', 'beneficiaries');
    }
}
