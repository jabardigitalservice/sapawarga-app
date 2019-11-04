<?php

use app\components\CustomMigration;

/**
 * Class m191104_042634_add_user_job_type_education_level */
class m191104_042934_add_user_job_type_education_level extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('user', 'job_type_id', $this->integer()->null()->after('address'));
        $this->addColumn('user', 'education_level_id', $this->integer()->null()->after('job_type_id'));

        $this->addForeignKey(
            'fk-user-job_type_id',
            'user',
            'job_type_id',
            'job_types',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-user-job_type_id', 'user');

        $this->dropColumn('user', 'education_level_id');
        $this->dropColumn('user', 'job_type_id');
    }
}
