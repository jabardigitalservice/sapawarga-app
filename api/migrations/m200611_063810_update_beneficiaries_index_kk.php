<?php

use app\components\CustomMigration;

/**
 * Class m200611_063810_update_beneficiaries_index_kk */
class m200611_063810_update_beneficiaries_index_kk extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex(
            'idx-beneficiaries-kk',
            'beneficiaries',
            ['no_kk', 'status']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-beneficiaries-kk', 'beneficiaries');
    }
}
