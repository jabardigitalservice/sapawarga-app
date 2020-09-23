<?php

use yii\db\Migration;
use yii\db\Query;

/**
 * Handles adding columns to table `{{%bansos_bnba_upload_histories}}`.
 */
class m200619_100643_add_tahap_bantuan_column_to_bansos_bnba_upload_histories_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
         $this->addColumn('bansos_bnba_upload_histories', 'tahap_bantuan', $this->integer());

        // set all existing tahap_bantuan in bansos_bnba_upload_histories to 2
        foreach ((new Query())->from('bansos_bnba_upload_histories')->each() as $row) {
            $this->update('bansos_bnba_upload_histories', ['tahap_bantuan' => 2], ['id' => $row['id']]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
          $this->dropColumn('bansos_bnba_upload_histories', 'tahap_bantuan');
    }
}
