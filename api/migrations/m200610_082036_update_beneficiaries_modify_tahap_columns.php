<?php

use app\components\CustomMigration;

/**
 * Class m200610_082036_update_beneficiaries_modify_tahap_columns */
class m200610_082036_update_beneficiaries_modify_tahap_columns extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // add new columns
        $this->addColumn('beneficiaries', 'tahap_1_verval', $this->integer()->null()->after('is_tahap_1'));
        $this->addColumn('beneficiaries', 'is_tahap_1_penetapan', $this->boolean()->defaultValue(false)->after('tahap_1_verval'));

        $this->addColumn('beneficiaries', 'tahap_2_verval', $this->integer()->null()->after('is_tahap_1_penetapan'));
        $this->addColumn('beneficiaries', 'is_tahap_2_penetapan', $this->boolean()->defaultValue(false)->after('tahap_2_verval'));

        $this->addColumn('beneficiaries', 'tahap_3_verval', $this->integer()->null()->after('is_tahap_2_penetapan'));
        $this->addColumn('beneficiaries', 'is_tahap_3_penetapan', $this->boolean()->defaultValue(false)->after('tahap_3_verval'));

        $this->addColumn('beneficiaries', 'tahap_4_verval', $this->integer()->null()->after('is_tahap_3_penetapan'));
        $this->addColumn('beneficiaries', 'is_tahap_4_penetapan', $this->boolean()->defaultValue(false)->after('tahap_4_verval'));

        // populate new columns
        \Yii::$app->db->createCommand('UPDATE beneficiaries SET tahap_1_verval = status_verification WHERE is_tahap_1 = 1')
            ->execute();

        \Yii::$app->db->createCommand('UPDATE beneficiaries SET is_tahap_1_penetapan = 1 WHERE is_tahap_1 = 1 AND status_verification = 3')
            ->execute();

        // drop old columns
        $this->dropColumn('beneficiaries', 'is_tahap_1');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // create old columns
        $this->addColumn('beneficiaries', 'is_tahap_1', $this->integer()->null()->after('updated_at'));

        // populate old columns
        \Yii::$app->db->createCommand('UPDATE beneficiaries SET is_tahap_1 = 1 WHERE tahap_1_verval IS NOT NULL')
            ->execute();

        // drop new columns
        $this->dropColumn('beneficiaries', 'is_tahap_4_penetapan');
        $this->dropColumn('beneficiaries', 'tahap_4_verval');

        $this->dropColumn('beneficiaries', 'is_tahap_3_penetapan');
        $this->dropColumn('beneficiaries', 'tahap_3_verval');

        $this->dropColumn('beneficiaries', 'is_tahap_2_penetapan');
        $this->dropColumn('beneficiaries', 'tahap_2_verval');

        $this->dropColumn('beneficiaries', 'is_tahap_1_penetapan');
        $this->dropColumn('beneficiaries', 'tahap_1_verval');
    }
}
