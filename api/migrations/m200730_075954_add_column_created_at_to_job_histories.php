<?php

use app\components\CustomMigration;
use yii\db\query;

/**
 * Class m200625_075954_add_column_error_to_job_histories */
class m200730_075954_add_column_created_at_to_job_histories extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
         $this->addColumn('bansos_bnba_download_histories', 'created_at', $this->integer()->defaultValue(null));
         $this->addColumn('bansos_verval_download_histories', 'created_at', $this->integer()->defaultValue(null));

         // migrate column values
         $query = <<<SQL
            UPDATE %s SET created_at =
            CASE
              WHEN start_at IS NOT NULL THEN start_at
              ELSE 0
            END
            ;
SQL;
         $this->execute(sprintf($query, 'bansos_verval_download_histories'));
         $this->execute(sprintf($query, 'bansos_bnba_download_histories'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
         $this->dropColumn('bansos_bnba_download_histories', 'created_at');
         $this->dropColumn('bansos_verval_download_histories', 'created_at');
    }
}
