<?php

use app\components\CustomMigration;

/**
 * Class m190912_042000_update_user_add_profile_updated_at */
class m190912_042000_update_user_add_profile_updated_at extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('user', 'profile_updated_at', $this->integer()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('user', 'profile_updated_at');
    }
}
