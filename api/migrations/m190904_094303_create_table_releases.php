<?php

use app\components\CustomMigration;

/**
 * Class m190904_094303_create_table_releases */
class m190904_094303_create_table_releases extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('releases', [
            'id'            => $this->primaryKey(),
            'version'       => $this->string()->notNull()->unique(),
            'force_update'  => $this->boolean()->notNull(),
            'created_by'    => $this->integer()->null(),
            'updated_by'    => $this->integer()->null(),
            'created_at'    => $this->integer()->null(),
            'updated_at'    => $this->integer()->null(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('releases');
    }
}
