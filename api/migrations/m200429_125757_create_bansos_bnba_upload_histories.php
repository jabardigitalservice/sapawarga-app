<?php

use app\components\CustomMigration;

/**
 * Class m200429_125757_create_bansos_bnba_upload_histories */
class m200429_125757_create_bansos_bnba_upload_histories extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('bansos_bnba_upload_histories', [
            'id'           => $this->primaryKey(),
            'user_id'      => $this->integer(),
            'bansos_type'  => $this->integer()->notNull(),
            'kabkota_code' => $this->string(4),
            'kec_code'     => $this->string(8),
            'kel_code'     => $this->string(12),
            'file_path'    => $this->string(),
            'status'       => $this->integer()->notNull(),
            'notes'        => $this->string(),
            'created_by'   => $this->integer(),
            'updated_by'   => $this->integer(),
            'created_at'   => $this->integer()->notNull(),
            'updated_at'   => $this->integer()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('bansos_bnba_upload_histories');
    }
}
