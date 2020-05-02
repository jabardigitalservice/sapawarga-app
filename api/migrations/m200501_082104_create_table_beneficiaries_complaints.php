<?php

use app\components\CustomMigration;

/**
 * Class m200501_082104_create_table_beneficiaries_complaints */
class m200501_082104_create_table_beneficiaries_complaints extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('beneficiaries_complain', [
            'id' => $this->primaryKey(),
            'beneficiaries_id' => $this->integer(),

            'name' => $this->string()->notNull(),
            'phone' => $this->string(),
            'address' => $this->string(),

            'kabkota_bps_id' => $this->string(4),
            'kec_bps_id' => $this->string(8),
            'kel_bps_id' => $this->string(12),
            'kabkota_bps_name' => $this->string(),
            'kec_bps_name' => $this->string(),
            'kel_bps_name' => $this->string(),
            'rt' => $this->string(5),
            'rw' => $this->string(5),

            'notes_reason' => $this->string(),

            'ip_address' => $this->string(),
            'status' => $this->integer()->notNull(),

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
        $this->dropTable('beneficiaries_complain');
    }
}
