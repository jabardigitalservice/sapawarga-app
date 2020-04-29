<?php

use app\components\CustomMigration;

/**
 * Class m200429_132739_set_nik_column_null_in_beneficiaries_table */
class m200429_132739_set_nik_column_null_in_beneficiaries_table extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('beneficiaries', 'nik', $this->string(100)->null());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('beneficiaries', 'nik', $this->string(100)->notNull());
    }
}
