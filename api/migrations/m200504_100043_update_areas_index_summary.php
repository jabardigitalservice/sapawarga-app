<?php

use app\components\CustomMigration;

/**
 * Class m200504_100043_update_areas_index_summary */
class m200504_100043_update_areas_index_summary extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $this->createIndex(
            'idx-areas-bps-parent',
            'areas',
            ['code_bps_parent']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-areas-bps-parent', 'areas');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m200504_100043_update_areas_index_summary cannot be reverted.\n";

        return false;
    }
    */
}
