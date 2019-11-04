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
        $this->addColumn('user', 'job_type_id', $this->smallInteger()->unsigned()->null()->after('address'));
        $this->addColumn('user', 'education_level_id', $this->smallInteger()->unsigned()->null()->after('job_type_id'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('user', 'education_level_id');
        $this->dropColumn('user', 'job_type_id');
    }
}
