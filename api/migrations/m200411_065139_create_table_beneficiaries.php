<?php

use app\components\CustomMigration;

/**
 * Class m200411_065139_create_table_beneficiaries */
class m200411_065139_create_table_beneficiaries extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('beneficiaries', [
            'id' => $this->primaryKey(),
            'nik' => $this->string(),
            'no_kk' => $this->string(),
            'name' => $this->string()->notNull(),
            'kabkota_bps_id' => $this->string(),
            'kec_bps_id' => $this->string(),
            'kel_bps_id' => $this->string(),
            'kabkota_id' => $this->integer(),
            'kec_id' => $this->integer(),
            'kel_id' => $this->integer(),
            'rt' => $this->string(),
            'rw' => $this->string(),
            'address' => $this->string(),
            'phone' => $this->string(),
            'total_family_members' => $this->integer(),
            'job_type_name' => $this->string(),
            'job_status' => $this->string(),
            'income_before' => $this->integer(),
            'income_after' => $this->integer(),
            'image_ktp' => $this->string(),
            'image_kk' => $this->string(),
            'status_verification' => $this->integer()->notNull(),
            'status' => $this->integer()->notNull(),
            'notes' => $this->string(),

            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('beneficiaries');
    }
}
