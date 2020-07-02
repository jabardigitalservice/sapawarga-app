<?php

use app\components\CustomMigration;

/**
 * Class m200625_075954_add_column_error_to_job_histories */
class m200625_075954_add_column_error_to_job_histories extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
         $this->addColumn('bansos_bnba_download_histories', 'errors', $this->json());
         $this->addColumn('bansos_verval_download_histories', 'errors', $this->json());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
         $this->dropColumn('bansos_bnba_download_histories', 'errors');
         $this->dropColumn('bansos_verval_download_histories', 'errors');
    }
}
