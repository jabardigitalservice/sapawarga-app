<?php

use app\components\CustomMigration;

/**
 * Class m200504_095526_update_beneficiaries_index_summary */
class m200504_095526_update_beneficiaries_index_summary extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $this->createIndex(
            'idx-beneficiaries-kabkota',
            'beneficiaries',
            ['domicile_kabkota_bps_id' , 'domicile_kec_bps_id', 'status_verification', 'status']
        );
        $this->createIndex(
            'idx-beneficiaries-kec',
            'beneficiaries',
            [ 'domicile_kec_bps_id' , 'domicile_kel_bps_id' , 'status_verification', 'status']
        );
        $this->createIndex(
            'idx-beneficiaries-kel',
            'beneficiaries',
            ['domicile_kel_bps_id' , 'domicile_rw', 'domicile_rt', 'status_verification', 'status']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-beneficiaries-kabkota', 'beneficiaries');
        $this->dropIndex('idx-beneficiaries-kec', 'beneficiaries');
        $this->dropIndex('idx-beneficiaries-kel', 'beneficiaries');
    }
}
