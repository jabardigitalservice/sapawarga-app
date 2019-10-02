<?php

use app\components\CustomMigration;

/**
 * Class m191002_064250_add_user_last_access */
class m191002_064250_add_user_last_access extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('user', 'last_access_at', $this->timestamp()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('user', 'last_access_at');
    }
}
