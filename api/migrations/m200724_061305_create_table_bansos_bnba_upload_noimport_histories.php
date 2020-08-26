<?php

use app\components\CustomMigration;

/**
 * Class m200724_061305_create_table_bansos_bnba_upload_noimport_histories */
class m200724_061305_create_table_bansos_bnba_upload_noimport_histories extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('bansos_bnba_upload_noimport_histories', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(),
            'kabkota_name' => $this->string(200)->defaultValue(null),
            'original_filename' => $this->string(200)->defaultValue(null),
            'final_url' => $this->string(200)->defaultValue(null),
            'status' => $this->integer()->defaultValue(null),
            'timestamp' => $this->integer()->defaultValue(null),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('bansos_bnba_upload_noimport_histories');
    }
}
