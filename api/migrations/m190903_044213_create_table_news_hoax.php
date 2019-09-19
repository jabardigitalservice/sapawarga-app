<?php

use app\components\CustomMigration;

/**
 * Class m190903_044213_create_table_news_hoax */
class m190903_044213_create_table_news_hoax extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('news_hoax', [
            'id'          => $this->primaryKey(),
            'category_id' => $this->integer()->notNull(),
            'title'       => $this->string()->notNull(),
            'slug'        => $this->string()->null(),
            'cover_path'  => $this->string()->notNull(),
            'source_url'  => $this->string()->null(),
            'source_date' => $this->date()->null(),
            'content'     => $this->text()->notNull(),
            'meta'        => $this->json()->null(),
            'seq'         => $this->integer()->null(),
            'status'      => $this->integer()->notNull(),
            'created_at'  => $this->integer()->null(),
            'updated_at'  => $this->integer()->null(),
        ]);

        $this->addForeignKey(
            'fk-news_hoax-category_id',
            'news_hoax',
            'category_id',
            'categories',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'fk-news_hoax-category_id',
            'news_hoax'
        );

        $this->dropTable('news_hoax');
    }
}
