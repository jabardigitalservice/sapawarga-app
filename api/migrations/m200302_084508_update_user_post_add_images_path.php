<?php

use app\components\CustomMigration;

/**
 * Class m200302_084508_update_user_post_add_images_path */
class m200302_084508_update_user_post_add_images_path extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('user_posts', 'images', $this->json()->after('image_path'));

        \Yii::$app->db->createCommand('
                UPDATE user_posts
                set images = COALESCE(CONCAT(\'[{"path":"\', image_path, \'"}]\'), null)
            ')->execute();

        $this->dropColumn('user_posts', 'image_path');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('user_posts', 'image_path', $this->text()->after('tags'));
        $this->dropColumn('user_posts', 'images');
    }
}
