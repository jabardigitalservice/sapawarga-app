<?php

use app\components\CustomMigration;

/**
 * Class m210707_011535_add_unique_to_phone_from_user_table */
class m210707_011535_add_unique_to_phone_from_user_table extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropIndex('idx-user-unique', 'user');

        // add unique constraint for column unique_id, username and phone
        $this->createIndex(
            'idx-user-unique',
            'user',
            ['unique_id', 'username', 'phone'],
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-user-unique', 'user');

        // add unique constraint for column username
        $this->createIndex(
            'idx-user-unique',
            'user',
            'username',
            true
        );
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210707_011535_add_unique_to_phone_from_user_table cannot be reverted.\n";

        return false;
    }
    */
}
