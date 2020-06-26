<?php

use app\components\CustomMigration;

/**
 * Class m200623_043816_create_table_bansos_verval_download_histories */
class m200623_043816_create_table_bansos_verval_download_histories extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('bansos_verval_download_histories', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(),
            'job_id' => $this->integer()->defaultValue(null),
            'row_count' => $this->bigInteger()->defaultValue(0),
            'row_processed' => $this->bigInteger()->defaultValue(0),
            'final_url' => $this->string(200)->defaultValue(null),
            'params' => $this->json(),
            'start_at' => $this->integer()->defaultValue(null),
            'done_at' => $this->integer()->defaultValue(null),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('bansos_verval_download_histories');
    }
}
