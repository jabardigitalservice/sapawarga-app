<?php

use app\components\CustomMigration;

/**
 * Class m200205_070819_update_user_post_add_likes_comment_count */
class m200205_070819_update_user_post_add_likes_comment_count extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('user_posts', 'comments_count', $this->integer()->notNull()->defaultValue(0)->after('last_user_post_comment_id'));
        $this->addColumn('user_posts', 'likes_count', $this->integer()->notNull()->defaultValue(0)->after('last_user_post_comment_id'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('user_posts', 'comments_count');
        $this->dropColumn('user_posts', 'likes_count');
    }
}
