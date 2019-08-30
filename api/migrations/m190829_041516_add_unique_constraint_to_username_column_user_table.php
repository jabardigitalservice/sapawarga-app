<?php

use app\components\CustomMigration;

/**
 * Class m190829_041516_add_unique_constraint_to_username_column_user_table */
class m190829_041516_add_unique_constraint_to_username_column_user_table extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // drop existing index for table user
        $this->dropIndex('idx-user', 'user');

        // recreate index for column auth_key, password_hash, and status
        $this->createIndex(
            'idx-user',
            'user',
            ['auth_key', 'password_hash', 'status']
        );

        // create index for column username
        $this->createIndex(
            'idx-user-unique',
            'user',
            'username',
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-user-unique', 'user');
        $this->dropIndex('idx-user', 'user');

        $this->createIndex(
            'idx-user',
            'user',
            ['username', 'auth_key', 'password_hash', 'status']
        );
    }
}
