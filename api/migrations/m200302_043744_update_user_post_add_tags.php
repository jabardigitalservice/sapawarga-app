<?php

use app\components\CustomMigration;

/**
 * Class m200302_043744_update_user_post_add_tags */
class m200302_043744_update_user_post_add_tags extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('user_posts', 'tags', $this->text()->after('text'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('user_posts', 'tags');
    }
}
