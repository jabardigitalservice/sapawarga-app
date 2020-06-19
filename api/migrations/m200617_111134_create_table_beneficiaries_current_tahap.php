<?php

use app\components\CustomMigration;

/**
 * Class m200617_111134_create_table_beneficiaries_current_tahap */
class m200617_111134_create_table_beneficiaries_current_tahap extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('beneficiaries_current_tahap', [
            'id' => $this->primaryKey(),
            'current_tahap' => $this->tinyInteger()->defaultValue(1)->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('beneficiaries_current_tahap');
    }
}
