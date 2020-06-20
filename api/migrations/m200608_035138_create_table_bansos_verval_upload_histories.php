<?php

use app\components\CustomMigration;

/**
 * Class m200608_035138_create_table_bansos_verval_upload_histories */
class m200608_035138_create_table_bansos_verval_upload_histories extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('bansos_verval_upload_histories', [
            'id'                => $this->primaryKey(),
            'user_id'           => $this->integer(),
            'verval_type'       => $this->string(30),
            'kabkota_code'      => $this->string(4),
            'kec_code'          => $this->string(8),
            'kel_code'          => $this->string(12),
            'original_filename' => $this->string(),
            'file_path'         => $this->string(),
            'invalid_file_path' => $this->string(),
            'total_row'         => $this->integer(),
            'successed_row'     => $this->integer(),
            'status'            => $this->integer(),
            'notes'             => $this->string(),
            'created_by'        => $this->integer(),
            'updated_by'        => $this->integer(),
            'created_at'        => $this->integer(),
            'updated_at'        => $this->integer(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('bansos_verval_upload_histories');
    }
}
