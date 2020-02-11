<?php

use app\components\CustomMigration;

/**
 * Class m200210_103715_update_news_important_add_likes */
class m200210_103715_update_news_important_add_likes extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('news_important', 'likes_count', $this->integer()->notNull()->defaultValue(0)->after('kabkota_id'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('news_important', 'likes_count');
    }
}
