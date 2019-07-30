<?php

use app\components\CustomMigration;

/**
 * Handles adding kabkota_id to table `{{%news}}`.
 */
class m190730_041251_add_kabkota_id_column_to_news_table extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('news', 'kabkota_id', $this->integer()->null());

        $this->addForeignKey(
            'fk-news-kabkota_id',
            'news',
            'kabkota_id',
            'areas',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-news-kabkota_id', 'news');

        $this->dropColumn('news', 'kabkota_id');
    }
}
