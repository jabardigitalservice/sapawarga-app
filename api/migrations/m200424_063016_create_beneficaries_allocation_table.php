<?php

use yii\db\Migration;

/**
 * Handles the creation of table `beneficaries_allocation`.
 */
class m200424_063016_create_beneficaries_allocation_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('beneficiaries_allocation', [
            'id' => $this->primaryKey(),
            'nik' => $this->string(20)->notNull(),
            'no_kk' => $this->string(20),
            'name' => $this->string(200)->notNull(),
            'kabkota_bps_id' => $this->string(4),
            'kec_bps_id' => $this->string(8),
            'kel_bps_id' => $this->string(12),
            'rt' => $this->string(3),
            'rw' => $this->string(3),
            'address' => $this->string(),
            'phone' => $this->string(),
            'bansos_type' => $this->integer()->unsigned(),
            'status' => $this->integer()->notNull(),
            'notes' => $this->string(),

            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex(
            'idx-beneficiaries-allocation',
            'beneficiaries_allocation',
            ['nik', 'no_kk', 'name', 'kabkota_bps_id', 'kec_bps_id', 'kel_bps_id', 'rt', 'rw', 'bansos_type', 'status']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('beneficaries_allocation');
    }
}
