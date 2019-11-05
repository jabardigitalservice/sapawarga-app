<?php

use app\components\CustomMigration;

/**
 * Class m191029_084447_create_table_news_important */
class m191029_084447_create_table_news_important extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('news_important', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull(),
            'category_id' => $this->integer()->notNull(),
            'content' => $this->text()->notNull(),
            'image_path' => $this->string()->null(),
            'source_url' => $this->string()->null(),
            'status' => $this->integer()->notNull(),
            'created_by' => $this->integer()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_by' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->createTable('news_important_attachment', [
            'id' => $this->primaryKey(),
            'news_important_id' =>$this->integer()->notNull(),
            'file_path' => $this->string()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-news-important-category_id',
            'news_important',
            'category_id',
            'categories',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-news_important_attachment-news_important_id',
            'news_important_attachment',
            'news_important_id',
            'news_important',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('news_important_attachment');
        $this->dropTable('news_important');
    }
}
