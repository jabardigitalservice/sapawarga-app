<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%broadcasts}}`
 */
class m200120_014356_add_action_columns_to_broadcasts_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('broadcasts', 'type', $this->string(100)->null()->after('rw'));
        $this->addColumn('broadcasts', 'link_url', $this->string()->null()->after('type'));
        $this->addColumn('broadcasts', 'internal_object_type', $this->string()->null()->after('link_url'));
        $this->addColumn('broadcasts', 'internal_object_id', $this->integer()->null()->after('internal_object_type'));
        $this->addColumn('broadcasts', 'internal_object_name', $this->string()->null()->after('internal_object_id'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('broadcasts', 'internal_object_name');
        $this->dropColumn('broadcasts', 'internal_object_id');
        $this->dropColumn('broadcasts', 'internal_object_type');
        $this->dropColumn('broadcasts', 'link_url');
        $this->dropColumn('broadcasts', 'type');
    }
}
