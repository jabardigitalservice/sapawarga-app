<?php

use app\components\CustomMigration;

/**
 * Class m190712_033626_update_news_add_total_viewers */
class m190712_033626_update_news_add_total_viewers extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('news', 'total_viewers', $this->integer()->after('featured')->defaultValue(0));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('news', 'total_viewers');
    }
}
