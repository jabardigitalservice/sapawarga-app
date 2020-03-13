<?php

use app\components\CustomMigration;

/**
 * Handles adding is_push_notification column to table `{{%polling}}` and `{{%survey}}`.
 */
class m200213_022020_add_is_push_notification_column_to_polling_and_survey_tables extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('news', 'is_push_notification', $this->boolean()->defaultValue(true)->after('total_viewers'));
        $this->addColumn('videos', 'is_push_notification', $this->boolean()->defaultValue(true)->after('total_likes'));
        $this->addColumn('survey', 'is_push_notification', $this->boolean()->defaultValue(true)->after('end_date'));
        $this->addColumn('polling', 'is_push_notification', $this->boolean()->defaultValue(true)->after('end_date'));
        $this->addColumn('news_important', 'is_push_notification', $this->boolean()->defaultValue(true)->after('likes_count'));
        $this->addColumn('news_hoax', 'is_push_notification', $this->boolean()->defaultValue(true)->after('content'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('news_hoax', 'is_push_notification');
        $this->dropColumn('news_important', 'is_push_notification');
        $this->dropColumn('polling', 'is_push_notification');
        $this->dropColumn('survey', 'is_push_notification');
        $this->dropColumn('videos', 'is_push_notification');
        $this->dropColumn('news', 'is_push_notification');
    }
}
