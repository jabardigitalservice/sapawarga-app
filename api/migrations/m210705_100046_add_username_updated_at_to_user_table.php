<?php

use app\components\CustomMigration;

/**
 * Class m210705_100046_add_username_updated_at_to_user_table */
class m210705_100046_add_username_updated_at_to_user_table extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('user', 'username_updated_at', $this->timestamp()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('user', 'username_updated_at');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m210705_100046_add_username_updated_at_to_user_table cannot be reverted.\n";

        return false;
    }
    */
}
