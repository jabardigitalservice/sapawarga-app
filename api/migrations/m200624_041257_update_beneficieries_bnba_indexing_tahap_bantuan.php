<?php

use app\components\CustomMigration;

/**
 * Class m200624_041257_update_beneficieries_bnba_indexing_tahap_bantuan */
class m200624_041257_update_beneficieries_bnba_indexing_tahap_bantuan extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex(
            'idx-bnba-area',
            'beneficiaries_bnba_tahap_1',
            ['is_deleted', 'id_tipe_bansos', 'tahap_bantuan', 'kode_kab', 'kode_kec', 'kode_kel', 'rw', 'rt']
        );

        $this->createIndex(
            'idx-bnba-type',
            'beneficiaries_bnba_tahap_1',
            ['is_deleted', 'tahap_bantuan', 'id_tipe_bansos', 'is_dtks', 'kode_kab', 'kode_kec', 'kode_kel', 'rw', 'rt']
        );

        $this->createIndex(
            'idx-bnba-type-prov',
            'beneficiaries_bnba_tahap_1',
            ['is_deleted', 'tahap_bantuan', 'id_tipe_bansos', 'is_dtks', 'kode_kab']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-bnba-area', 'beneficiaries_bnba_tahap_1');
        $this->dropIndex('idx-bnba-type', 'beneficiaries_bnba_tahap_1');
        $this->dropIndex('idx-bnba-type-prov', 'beneficiaries_bnba_tahap_1');
    }
}
