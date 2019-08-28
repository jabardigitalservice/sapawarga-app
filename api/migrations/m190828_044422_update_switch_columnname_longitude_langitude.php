<?php

use app\components\CustomMigration;

/**
 * Class m190828_044422_update_switch_columnname_longitude_langitude */
class m190828_044422_update_switch_columnname_longitude_langitude extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameColumn('areas', 'longitude', 'longitude_rename');
        $this->renameColumn('areas', 'latitude', 'longitude');
        $this->renameColumn('areas', 'longitude_rename', 'latitude');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->renameColumn('areas', 'latitude', 'latitude_rename');
        $this->renameColumn('areas', 'longitude', 'latitude');
        $this->renameColumn('areas', 'latitude_rename', 'longitude');
    }
}
