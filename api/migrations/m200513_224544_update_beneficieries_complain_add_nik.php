<?php

use app\components\CustomMigration;

/**
 * Class m200513_224544_update_beneficieries_complain_add_nik */
class m200513_224544_update_beneficieries_complain_add_nik extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('beneficiaries_complain', 'nik', $this->string(20)->after('beneficiaries_id'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('beneficiaries_complain', 'nik');
    }
}
