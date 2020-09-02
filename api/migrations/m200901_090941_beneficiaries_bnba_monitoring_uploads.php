<?php

use app\components\CustomMigration;

/**
 * Class m200901_090941_beneficiaries_bnba_monitoring_uploads */
class m200901_090941_beneficiaries_bnba_monitoring_uploads extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('beneficiaries_bnba_monitoring_uploads', [
            'id' => $this->primaryKey(),
            'code_bps' => $this->integer()->defaultValue(null),
            'kabkota_name' => $this->string(150)->defaultValue(null),
            'tahap_bantuan' => $this->integer(2)->defaultValue(null),
            'is_dtks' => $this->integer(1)->defaultValue(null),
            'last_updated' => $this->integer()->defaultValue(null),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);


    }


    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('beneficiaries_bnba_monitoring_uploads');
    }
}
