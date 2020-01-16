<?php

use app\components\CustomMigration;

/**
 * Class m200113_074825_create_table_gamifications */
class m200113_074825_create_table_gamifications extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('gamifications', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull(),
            'title_badge' => $this->string()->notNull(),
            'image_badge_path' => $this->string()->notNull(),
            'description' => $this->text()->notNull(),
            'object_type' => $this->string()->notNull(),
            'object_event' => $this->string()->notNull(),
            'total_hit' => $this->integer()->notNull(),
            'status' => $this->integer()->notNull(),
            'start_date' => $this->date()->notNull(),
            'end_date' => $this->date()->notNull(),
            'created_by' => $this->integer()->notNull(),
            'updated_by' => $this->integer()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('gamifications');
    }
}
