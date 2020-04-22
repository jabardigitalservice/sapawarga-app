<?php

use app\components\CustomMigration;

/**
 * Class m200422_134224_update_beneficieries_list_indexing */
class m200422_134224_update_beneficieries_list_indexing extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex(
            'idx-beneficiaries-list',
            'beneficiaries',
            ['status', 'domicile_kel_bps_id', 'domicile_rw', 'status_verification']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-beneficiaries-list', 'beneficiaries');
    }
}
