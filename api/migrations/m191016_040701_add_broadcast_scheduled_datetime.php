<?php

use app\components\CustomMigration;

/**
 * Class m191016_040701_add_broadcast_scheduled_datetime */
class m191016_040701_add_broadcast_scheduled_datetime extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('broadcasts', 'scheduled_datetime', $this->timestamp()->null());
        $this->addColumn('broadcasts', 'created_by', $this->integer()->null());
        $this->addColumn('broadcasts', 'updated_by', $this->integer()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('broadcasts', 'scheduled_datetime');
        $this->dropColumn('broadcasts', 'created_by');
        $this->dropColumn('broadcasts', 'updated_by');
    }
}
