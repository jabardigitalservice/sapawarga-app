<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%bansos_bnba_upload_histories}}`.
 */
class m200519_080930_add_invalid_file_path_column_to_bansos_bnba_upload_histories_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('bansos_bnba_upload_histories', 'invalid_file_path', $this->string()->after('file_path'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('bansos_bnba_upload_histories', 'invalid_file_path');
    }
}
