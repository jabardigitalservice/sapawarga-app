<?php

use app\components\CustomMigration;

/**
 * Class m190726_065452_create_table_videos */
class m190726_065452_create_table_videos extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('videos', [
            'id' => $this->primaryKey(),
            'category_id' => $this->integer()->notNull(),
            'title' => $this->string()->notNull(),
            'source' => $this->string()->notNull(),
            'video_url' => $this->string()->notNull(),
            'kabkota_id' => $this->integer()->null(),
            'total_likes' => $this->integer()->defaultValue(0),
            'seq' => $this->integer()->null(),
            'status' => $this->integer()->notNull(),
            'created_by' => $this->integer()->notNull(),
            'updated_by' => $this->integer()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->createTable('likes', [
            'id' => $this->primaryKey(),
            'type' => $this->string()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'entity_id' => $this->integer()->notNull(),
            'created_at'  => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('videos');
        $this->dropTable('likes');
    }
}
