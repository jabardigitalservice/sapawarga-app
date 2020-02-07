<?php

use yii\db\Migration;

/**
 * Handles adding public_source_url column to table `{{%news_important}}`.
 */
class m200206_092119_add_public_source_url_column_to_news_important_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('news_important', 'public_source_url', $this->string()->null()->after('source_url'));

        // Set `public_source_url` values for existing `news_important` rows
        \Yii::$app->db->createCommand('UPDATE news_important SET public_source_url=concat(:prefix, news_important.id)')
            ->bindValue(':prefix', getenv('FRONTEND_URL') . '/#/info-penting?id=')
            ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('news_important', 'public_source_url');
    }
}
