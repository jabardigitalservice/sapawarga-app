<?php

use app\components\CustomMigration;

/**
 * Class m200713_015445_update_beneficiaries_bnba_indexing_monitoring_upload */
class m200713_015445_update_beneficiaries_bnba_indexing_monitoring_upload extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex(
            'idx-monitoring-upload-bnba',
            'beneficiaries_bnba_tahap_1',
            ['is_deleted', 'tahap_bantuan', 'is_dtks', 'kode_kab', 'updated_time']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-monitoring-upload-bnba', 'beneficiaries_bnba_tahap_1');
    }

}
