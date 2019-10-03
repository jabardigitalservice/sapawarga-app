<?php

use app\components\CustomMigration;

/**
 * Class m191003_060025_add_user_account_confirmed_at */
class m191003_060025_add_user_account_confirmed_at extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('user', 'account_confirmed_at', $this->timestamp()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('user', 'account_confirmed_at');
    }
}
