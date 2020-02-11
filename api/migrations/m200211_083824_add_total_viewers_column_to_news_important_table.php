<?php

use yii\db\Migration;

/**
 * Handles adding total_viewers column to table `{{%news_important}}`.
 */
class m200211_083824_add_total_viewers_column_to_news_important_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('news_important', 'total_viewers', $this->integer()->notNull()->defaultValue(0)->after('kabkota_id'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('news_important', 'total_viewers');
    }
}
