<?php

use app\components\CustomMigration;

/**
 * Class m200420_171857_update_beneficieries_indexing */
class m200420_171857_update_beneficieries_indexing extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex(
            'idx-beneficiaries',
            'beneficiaries',
            ['nik' , 'domicile_kabkota_bps_id' , 'domicile_kec_bps_id' , 'domicile_kel_bps_id' , 'domicile_rw', 'status_verification', 'status']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-beneficiaries', 'beneficiaries');
    }
}
