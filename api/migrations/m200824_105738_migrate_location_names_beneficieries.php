<?php

use app\components\CustomMigration;

/**
 * Class m200824_105738_migrate_location_names_beneficieries */
class m200824_105738_migrate_location_names_beneficieries extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Migrate location name
        \Yii::$app->db->createCommand('
            UPDATE beneficiaries b
            INNER JOIN areas kabkota ON kabkota.code_bps = b.domicile_kabkota_bps_id
            INNER JOIN areas kec ON kec.code_bps = b.domicile_kec_bps_id
            INNER JOIN areas kel ON kel.code_bps = b.domicile_kel_bps_id
            SET
            domicile_kabkota_name = kabkota.name,
            domicile_kec_name = kec.name,
            domicile_kel_name = kel.name
        ')->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Rollback to default value
        \Yii::$app->db->createCommand('
            UPDATE beneficiaries
            SET
            domicile_kabkota_name = NULL,
            domicile_kec_name = NULL,
            domicile_kel_name = NULL
        ')->execute();
    }
}
