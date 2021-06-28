<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%user}}`.
 */
class m210628_075457_add_update_columns_to_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('user', 'is_username_updated', $this->tinyInteger(1)->defaultValue(0));
        $this->addColumn('user', 'username_update_popup_at', $this->dateTime()->defaultValue(date('Y-m-d H:i:s')));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('user', 'is_username_updated');
        $this->dropColumn('user', 'username_update_popup_at');
    }
}
