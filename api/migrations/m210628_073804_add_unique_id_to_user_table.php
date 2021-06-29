<?php

use app\components\CustomMigration;

/**
 * Class m210628_073804_add_unique_id_to_user_table */
class m210628_073804_add_unique_id_to_user_table extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('user', 'unique_id', $this->string()->after('id')->defaultValue(null));
        // populate column unique_id from username
        \Yii::$app->db->createCommand('UPDATE user SET unique_id = username where role=50')
            ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('user', 'unique_id');
    }
}
