<?php

use app\components\CustomMigration;

/**
 * Class m191016_134108_create_table_popups */
class m191016_134108_create_table_popups extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('popups', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull(),
            'image_path' => $this->string()->notNull(),
            'type' => $this->string(100)->notNull(), // external or feature
            'link_url' => $this->string()->null(),
            'internal_category' => $this->string()->null(),
            'internal_entity_id' => $this->integer()->null(),
            'internal_entity_name' => $this->string()->null(),
            'status' => $this->integer()->notNull(),
            'start_date' => $this->dateTime()->notNull(),
            'end_date' => $this->dateTime()->notNull(),
            'created_by' => $this->integer()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_by' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('popups');
    }
}
