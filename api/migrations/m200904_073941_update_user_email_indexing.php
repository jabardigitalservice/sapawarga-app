<?php

use app\components\CustomMigration;

/**
 * Class m200904_073941_update_user_email_indexing */
class m200904_073941_update_user_email_indexing extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex('idx-email', 'user', ['email']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-email', 'user');
    }
}
