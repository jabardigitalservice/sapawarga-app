<?php

use Yii;
use yii\db\Migration;

/**
 * Handles adding columns to table `{{%queue}}`.
 */
class m200729_094500_create_table_queue_details extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('queue_details', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer(),
            'job_type' => $this->string(100)->defaultValue(null),
            'params' => $this->json(),
            'results' => $this->json(),
            'status' => $this->integer()->defaultValue(null),
            'notes' => $this->text()->defaultValue(null),
            'total_row' => $this->bigInteger()->defaultValue(0),
            'processed_row' => $this->bigInteger()->defaultValue(0),
            'logs' => $this->text()->defaultValue(null),
            'created_at' => $this->integer()->defaultValue(null),
            'start_at' => $this->integer()->defaultValue(null),
            'done_at' => $this->integer()->defaultValue(null),
        ]);

        // migrate data from old bansos download histories tables
        foreach((new yii\db\Query)->from('bansos_verval_download_histories')->each() as $row) {
            $this->insertQueueDetail($row);
        }
        foreach((new yii\db\Query)->from('bansos_bnba_download_histories')->each() as $row) {
            $this->insertQueueDetail($row);
        }

        // drop old bansos download histories tables
        //$this->dropTable('bansos_bnba_download_histories');
        //$this->dropTable('bansos_verval_download_histories');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
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
            'errors' => $this->json(),
            'export_type' => $this->string(100)->defaultValue('bnba'),
        ]);
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
            'errors' => $this->json(),
        ]);

        $this->dropTable('queue_details');
    }

    /**
     * Simple wrapper for inserting new queue detail migrations
     */
    public function insertQueueDetail($row, $job_type = null)
    {
        if ($job_type == null) {
            if (isset($row['export_type'])) {
                $job_type = $row['export_type'];
            } else {
                $job_type = 'verval';
            }
        }

        $status = null;
        $notes = null;
        $results = [];
        $logs = 'Job id : '.$row['job_id']. PHP_EOL;

        if (!empty($row['done_at'])) {
            if (empty($row['errors']) && !empty($row['final_url']) ) {
                $status = 10; // sukses
                $notes = 'sukses';
                $results = [ 'final_url' => $row['final_url'] ];
            } else {
                $status = 20; // ada error
                $notes = 'terjadi masalah';
                $logs .= $row['errors'];
            }
        }

        $this->insert('queue_details', [
            'user_id' => $row['user_id'],
            'job_type' => $job_type,
            'params' => json_decode($row['params']),
            'results' => $results,
            'status' => $status,
            'notes' => $notes,
            'total_row' => $row['row_count'],
            'processed_row' => $row['row_processed'],
            'logs' => $logs,
            'created_at' => $row['start_at'],
            'start_at' => $row['start_at'],
            'done_at' => $row['done_at'],
        ]);
    }
}
