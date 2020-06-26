<?php

use app\components\CustomMigration;

/**
 * Class m200622_071338_update_beneficiaries_statistic_public_add_tahap_bantuan */
class m200622_071338_update_beneficiaries_statistic_public_add_tahap_bantuan extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
         $this->addColumn('beneficiaries_bnba_statistic_type', 'tahap_bantuan', $this->tinyInteger(1)->null()->after('rt'));
         $this->addColumn('beneficiaries_bnba_statistic_area', 'tahap_bantuan', $this->tinyInteger(1)->null()->after('rt'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
          $this->dropColumn('beneficiaries_bnba_statistic_type', 'tahap_bantuan');
          $this->dropColumn('beneficiaries_bnba_statistic_area', 'tahap_bantuan');
    }
}
