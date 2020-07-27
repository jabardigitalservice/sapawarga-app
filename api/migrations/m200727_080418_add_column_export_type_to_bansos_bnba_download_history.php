<?php

use app\components\CustomMigration;

/**
 * Class m200727_080418_add_column_export_type_to_bansos_bnba_download_history */
class m200727_080418_add_column_export_type_to_bansos_bnba_download_history extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
         $this->addColumn('bansos_bnba_download_histories', 'export_type', $this->string(100)->defaultValue('bnba'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
         $this->dropColumn('bansos_bnba_download_histories', 'export_type');
    }

}
