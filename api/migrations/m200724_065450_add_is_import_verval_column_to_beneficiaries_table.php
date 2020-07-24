<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%beneficiaries}}`.
 */
class m200724_065450_add_is_import_verval_column_to_beneficiaries_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('beneficiaries', 'is_import_verval', $this->boolean()->notNull()->defaultValue(false));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('beneficiaries', 'is_import_verval');
    }
}
