<?php

use app\components\CustomMigration;

/**
 * Class m191016_103328_add_fields_to_aspirasi */
class m191016_103328_add_fields_to_aspirasi extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('aspirasi', 'submitted_at', $this->integer()->null());
        $this->addColumn('aspirasi', 'last_revised_at', $this->integer()->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('aspirasi', 'last_revised_at');
        $this->dropColumn('aspirasi', 'submitted_at');
    }
}
