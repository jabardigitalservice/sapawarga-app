<?php

use app\components\CustomMigration;

/**
 * Class m200426_130125_create_beneficaries_nik_logs */
class m200426_130125_create_beneficaries_nik_logs extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('beneficiaries_nik_logs', [
            'id'         => $this->primaryKey(),
            'user_id'    => $this->integer(),
            'nik'        => $this->string(50)->notNull(),
            'ip_address' => $this->string(),
            'status'     => $this->integer()->notNull(),
            'notes'      => $this->string(),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex(
            'idx-beneficiaries-nik',
            'beneficiaries_nik_logs',
            ['user_id', 'nik', 'ip_address', 'status']
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('beneficiaries_nik_logs');
    }
}
