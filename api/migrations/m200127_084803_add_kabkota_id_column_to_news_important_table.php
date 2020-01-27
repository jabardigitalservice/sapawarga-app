<?php

use yii\db\Migration;

/**
 * Handles adding kabkota_id to table `{{%news_important}}`.
 */
class m200127_084803_add_kabkota_id_column_to_news_important_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('news_important', 'kabkota_id', $this->integer()->after('source_url')->null());

        $this->addForeignKey(
            'fk-news_important-kabkota_id',
            'news_important',
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
        $this->dropForeignKey('fk-news_important-kabkota_id', 'news_important');

        $this->dropColumn('news_important', 'kabkota_id');
    }
}
