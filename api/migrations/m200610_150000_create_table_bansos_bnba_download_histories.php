<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%queue}}`.
 */
class m200610_150000_create_table_bansos_bnba_download_histories extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('bansos_bnba_download_histories', [
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
        $this->dropTable('bansos_bnba_download_histories');
    }
}
