<?php

use app\components\CustomMigration;

/**
 * Class m200608_055538_update_beneficiaries_index_approval_berjenjang */
class m200608_055538_update_beneficiaries_index_approval_berjenjang extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex(
            'idx-beneficiaries-approval-kec',
            'beneficiaries',
            ['status', 'domicile_kec_bps_id' , 'status_verification']
        );
        $this->createIndex(
            'idx-beneficiaries-approval-kabkota',
            'beneficiaries',
            ['status', 'domicile_kabkota_bps_id' , 'status_verification']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-beneficiaries-approval-kabkota', 'beneficiaries');
        $this->dropIndex('idx-beneficiaries-approval-kec', 'beneficiaries');
    }
}
