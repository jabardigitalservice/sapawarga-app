<?php

use app\components\CustomMigration;

/**
 * Class m210708_032027_change_type_data_username_updated_at_from_user_table */
class m210708_032027_change_type_data_username_updated_at_from_user_table extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('user', 'username_updated_at', $this->integer()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        //
    }
}
