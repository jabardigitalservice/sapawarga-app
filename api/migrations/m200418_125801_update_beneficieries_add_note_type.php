<?php

use app\components\CustomMigration;

/**
 * Class m200418_125801_update_beneficieries_add_note_type */
class m200418_125801_update_beneficieries_add_note_type extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {

        $this->addColumn('beneficiaries', 'is_need_help', $this->integer(1)->after('image_kk'));
        $this->addColumn('beneficiaries', 'is_poor_new', $this->integer(1)->after('is_need_help'));
        $this->addColumn('beneficiaries', 'notes_approved', $this->text()->after('notes'));
        $this->addColumn('beneficiaries', 'notes_rejected', $this->text()->after('notes'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('beneficiaries', 'is_need_help');
        $this->dropColumn('beneficiaries', 'is_poor_new');
        $this->dropColumn('beneficiaries', 'note_approved');
        $this->dropColumn('beneficiaries', 'note_rejected');
    }
}
