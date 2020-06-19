<?php

use app\components\CustomMigration;

/**
 * Class m200619_052705_modify_table_beneficiaries_current_tahap */
class m200619_052705_modify_table_beneficiaries_current_tahap extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->renameColumn('beneficiaries_current_tahap', 'current_tahap', 'current_tahap_verval');
        $this->addColumn('beneficiaries_current_tahap', 'current_tahap_bnba', $this->tinyInteger()->defaultValue(1)->notNull());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('beneficiaries_current_tahap', 'current_tahap_bnba');
        $this->renameColumn('beneficiaries_current_tahap', 'current_tahap_verval', 'current_tahap');
    }
}
