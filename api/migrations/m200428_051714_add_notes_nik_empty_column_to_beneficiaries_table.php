<?php

use app\components\CustomMigration;

/**
 * Handles adding `notes_nik_empty` column to table `{{%beneficiaries}}`.
 */
class m200428_051714_add_notes_nik_empty_column_to_beneficiaries_table extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('beneficiaries', 'notes_nik_empty', $this->text()->after('notes_approved'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('beneficiaries', 'notes_nik_empty');
    }
}
