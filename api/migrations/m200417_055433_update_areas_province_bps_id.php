<?php

use app\components\CustomMigration;

/**
 * Class m200417_055433_update_areas_province_bps_id */
class m200417_055433_update_areas_province_bps_id extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('beneficiaries', 'province_bps_id', $this->string()->after('name'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('areas', 'province_bps_id');
    }
}
