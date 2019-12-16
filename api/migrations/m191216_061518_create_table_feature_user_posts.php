<?php

use app\components\CustomMigration;

/**
 * Class m191216_061518_create_table_feature_user_posts */
class m191216_061518_create_table_feature_user_posts extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('user_posts', [
            'id' => $this->primaryKey(),
            'text' => $this->string()->notNull(),
            'image_path' => $this->string()->notNull(),
            'last_user_post_id' => $this->integer(),
            'status' => $this->integer()->notNull(),
            'is_flagged' => $this->integer()->defaultValue(false),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->createTable('user_post_comments', [
            'id' => $this->primaryKey(),
            'user_post_id' => $this->integer(),
            'text' => $this->string()->notNull(),
            'status' => $this->integer()->notNull(),
            'is_flagged' => $this->integer()->defaultValue(false),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-user_posts-last_user_post_id',
            'user_posts',
            'last_user_post_id',
            'user_post_comments',
            'id',
            'SET NULL'
        );

        $this->addForeignKey(
            'fk-user_posts-created_by',
            'user_posts',
            'created_by',
            'user',
            'id',
            'SET NULL'
        );

        $this->addForeignKey(
            'fk-user_post_comments-user_post_id',
            'user_post_comments',
            'user_post_id',
            'user_posts',
            'id',
            'SET NULL'
        );

        $this->addForeignKey(
            'fk-user_post_comments-created_by',
            'user_post_comments',
            'created_by',
            'user',
            'id',
            'SET NULL'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-user_post_comments-created_by', 'user_post_comments');
        $this->dropForeignKey('fk-user_post_comments-user_post_id', 'user_post_comments');

        $this->dropForeignKey('fk-user_posts-created_by', 'user_posts');
        $this->dropForeignKey('fk-user_posts-last_user_post_id', 'user_posts');

        $this->dropTable('user_post_comments');
        $this->dropTable('user_posts');
    }
}
