<?php

use app\components\CustomMigration;

/**
 * Class m190906_091858_update_user_add_password_updated_at */
class m190906_091858_update_user_add_password_updated_at extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('user', 'password_updated_at', $this->integer()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('user', 'password_updated_at');
    }
}
