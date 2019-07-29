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
            'category_id' => $this->integer()->null(),
            'title' => $this->string()->null(),
            'source' => $this->string()->null(),
            'video_url' => $this->string()->null(),
            'kabkota_id' => $this->integer()->null(),
            'total_likes' => $this->integer()->null(),
            'seq' => $this->integer(3)->null(),
            'status' => $this->integer()->null(),
            'created_by' => $this->integer()->null(),
            'updated_by' => $this->integer()->null(),
            'created_at' => $this->integer()->null(),
            'updated_at' => $this->integer()->null(),
        ]);

        $this->createTable('video_likes', [
            'user_id' => $this->integer()->null(),
            'video_id' => $this->integer()->null(),
            'created_at'  => $this->integer()->null(),
            'updated_at' => $this->integer()->null(),
        ]);

        $this->addPrimaryKey('user_video_likes_pk', 'video_likes', ['user_id', 'video_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('videos');
        $this->dropTable('video_likes');
    }
}
