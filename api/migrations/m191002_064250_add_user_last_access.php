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
        $this->addColumn('user', 'last_access', $this->timestamp()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m191002_064250_add_user_last_access cannot be reverted.\n";

        return false;
    }
}
