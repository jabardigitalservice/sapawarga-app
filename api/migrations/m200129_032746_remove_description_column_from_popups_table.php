<?php

use yii\db\Migration;

/**
 * Handles removing description to table `{{%popups}}`.
 */
class m200129_032746_remove_description_column_from_popups_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('popups', 'description');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('popups', 'description', $this->text()->null()->after('title'));
    }
}
