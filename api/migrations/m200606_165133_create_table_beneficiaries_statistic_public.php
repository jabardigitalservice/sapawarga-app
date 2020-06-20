<?php

use app\components\CustomMigration;

/**
 * Class m200606_165133_create_table_beneficiaries_statistic_public */
class m200606_165133_create_table_beneficiaries_statistic_public extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('beneficiaries_bnba_statistic_type', [
            'id' => $this->primaryKey(),
            'id_tipe_bansos' => $this->integer(),
            'is_dtks' => $this->integer(),
            'total' => $this->integer(),
            'area_type' => $this->string(50),

            'kabkota_bps_id' => $this->integer(),
            'kec_bps_id' => $this->integer(),
            'kel_bps_id' => $this->integer(),
            'rw' => $this->integer(),
            'rt' => $this->integer(),

            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        $this->createTable('beneficiaries_bnba_statistic_area', [
            'id' => $this->primaryKey(),
            'code_bps' => $this->integer(),
            'total' => $this->integer(),
            'area_type' => $this->string(50),

            'kabkota_bps_id' => $this->integer(),
            'kec_bps_id' => $this->integer(),
            'kel_bps_id' => $this->integer(),
            'rw' => $this->integer(),
            'rt' => $this->integer(),

            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('beneficiaries_bnba_statistic_type');
        $this->dropTable('beneficiaries_bnba_statistic_area');
    }
}
