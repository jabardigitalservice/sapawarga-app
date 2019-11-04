<?php

use app\components\CustomMigration;

/**
 * Class m191104_042855_create_table_job_types */
class m191104_042855_create_table_job_types extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('job_types', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull(),
            'seq' => $this->integer()->unsigned()->defaultValue(0)->notNull(),
            'status' => $this->integer()->unsigned()->notNull(),
            'created_by' => $this->integer()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_by' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $now = time();

        $this->insert('job_types', [
            'title'      => 'Belum Bekerja',
            'seq'        => 0,
            'status'     => 10,
            'created_by' => 1,
            'created_at' => $now,
            'updated_by' => 1,
            'updated_at' => $now,
        ]);

        $this->insert('job_types', [
            'title'      => 'Ibu Rumah Tangga',
            'seq'        => 1,
            'status'     => 10,
            'created_by' => 1,
            'created_at' => $now,
            'updated_by' => 1,
            'updated_at' => $now,
        ]);

        $this->insert('job_types', [
            'title'      => 'Pelajar/Mahasiswa',
            'seq'        => 1,
            'status'     => 10,
            'created_by' => 1,
            'created_at' => $now,
            'updated_by' => 1,
            'updated_at' => $now,
        ]);

        $this->insert('job_types', [
            'title'      => 'Pegawai Negeri',
            'seq'        => 1,
            'status'     => 10,
            'created_by' => 1,
            'created_at' => $now,
            'updated_by' => 1,
            'updated_at' => $now,
        ]);

        $this->insert('job_types', [
            'title'      => 'Pegawai Swasta',
            'seq'        => 1,
            'status'     => 10,
            'created_by' => 1,
            'created_at' => $now,
            'updated_by' => 1,
            'updated_at' => $now,
        ]);

        $this->insert('job_types', [
            'title'      => 'Lainnya',
            'seq'        => 99,
            'status'     => 10,
            'created_by' => 1,
            'created_at' => $now,
            'updated_by' => 1,
            'updated_at' => $now,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('job_types');
    }
}
