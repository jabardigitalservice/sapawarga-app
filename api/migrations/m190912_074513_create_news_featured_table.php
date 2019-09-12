<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%news_featured}}`.
 */
class m190912_074513_create_news_featured_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('news_featured', [
            'id'          => $this->primaryKey(),
            'news_id'    => $this->integer()->notNull(),
            'kabkota_id' => $this->integer(),
            'seq'        => $this->integer()->notNull(),
            'created_at'  => $this->integer()->null(),
            'created_by'  => $this->integer()->null(),
            'updated_at'  => $this->integer()->null(),
            'updated_by'  => $this->integer()->null(),
        ]);

        $this->addForeignKey(
            'fk-news_featured-news_id',
            'news_featured',
            'news_id',
            'news',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('news_featured');
    }
}
