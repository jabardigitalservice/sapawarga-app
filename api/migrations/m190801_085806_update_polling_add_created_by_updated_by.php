<?php

use app\components\CustomMigration;

/**
 * Class m190801_085806_update_polling_add_created_by_updated_by */
class m190801_085806_update_polling_add_created_by_updated_by extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('polling', 'created_by', $this->integer()->after('status')->notNull());
        $this->addColumn('polling', 'updated_by', $this->integer()->after('status')->notNull());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('polling', 'created_by');
        $this->dropColumn('polling', 'updated_by');
    }
}
