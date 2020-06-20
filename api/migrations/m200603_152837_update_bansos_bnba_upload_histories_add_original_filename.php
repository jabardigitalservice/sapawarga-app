<?php

use app\components\CustomMigration;

/**
 * Class m200603_152837_update_bansos_bnba_upload_histories_add_original_filename */
class m200603_152837_update_bansos_bnba_upload_histories_add_original_filename extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('bansos_bnba_upload_histories', 'original_filename', $this->string()->after('file_path'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('bansos_bnba_upload_histories', 'original_filename');
    }
}
