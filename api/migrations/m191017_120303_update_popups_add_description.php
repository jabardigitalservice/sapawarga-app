<?php

use app\components\CustomMigration;

/**
 * Class m191017_120303_update_popups_add_description */
class m191017_120303_update_popups_add_description extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('popups', 'description', $this->text()->null()->after('title'));

        $this->renameColumn('popups', 'internal_category', 'internal_object_type');
        $this->renameColumn('popups', 'internal_entity_id', 'internal_object_id');
        $this->renameColumn('popups', 'internal_entity_name', 'internal_object_name');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('popups', 'description');

        // Will use of internal object, example : 'news', 'polling' ,'survey'
        $this->renameColumn('popups', 'internal_object_type', 'internal_category');
        $this->renameColumn('popups', 'internal_object_id', 'internal_entity_id');
        $this->renameColumn('popups', 'internal_object_name', 'internal_entity_name');
    }
}
