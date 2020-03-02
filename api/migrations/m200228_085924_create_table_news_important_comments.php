<?php

use app\components\CustomMigration;

/**
 * Class m200228_085924_create_table_news_important_comments */
class m200228_085924_create_table_news_important_comments extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('news_important_comments', [
            'id' => $this->primaryKey(),
            'news_important_id' => $this->integer(),
            'text' => $this->string()->notNull(),
            'status' => $this->integer()->notNull(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-news_important_comments-news_important_id',
            'news_important_comments',
            'news_important_id',
            'news_important',
            'id',
            'SET NULL'
        );

        $this->addForeignKey(
            'fk-news_important_comments-created_by',
            'news_important_comments',
            'created_by',
            'user',
            'id',
            'SET NULL'
        );

        $this->addForeignKey(
            'fk-news_important_comments-updated_by',
            'news_important_comments',
            'updated_by',
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
        $this->dropForeignKey('fk-news_important_comments-updated_by', 'news_important_comments');
        $this->dropForeignKey('fk-news_important_comments-created_by', 'news_important_comments');
        $this->dropForeignKey('fk-news_important_comments-news_important_id', 'news_important_comments');

        $this->dropTable('news_important_comments');
    }
}
