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
        $this->addColumn('broadcasts', 'is_scheduled', $this->boolean()->defaultValue(false));
        $this->addColumn('broadcasts', 'scheduled_datetime', $this->integer()->unsigned()->null());
        $this->addColumn('broadcasts', 'created_by', $this->integer()->null());
        $this->addColumn('broadcasts', 'updated_by', $this->integer()->null());

        $this->addForeignKey(
            'fk-broadcasts-created_by',
            'broadcasts',
            'created_by',
            'user',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-broadcasts-updated_by',
            'broadcasts',
            'updated_by',
            'user',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-broadcasts-created_by', 'broadcasts');
        $this->dropForeignKey('fk-broadcasts-updated_by', 'broadcasts');

        $this->dropColumn('broadcasts', 'is_scheduled');
        $this->dropColumn('broadcasts', 'scheduled_datetime');
        $this->dropColumn('broadcasts', 'created_by');
        $this->dropColumn('broadcasts', 'updated_by');
    }
}
