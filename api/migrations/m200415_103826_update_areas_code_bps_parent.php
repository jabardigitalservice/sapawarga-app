<?php

use app\components\CustomMigration;

/**
 * Class m200415_103826_update_areas_code_bps_parent */
class m200415_103826_update_areas_code_bps_parent extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('beneficiaries', 'domicile_kel_bps_id', $this->string());
        $this->alterColumn('beneficiaries', 'domicile_kec_bps_id', $this->string());
        $this->alterColumn('beneficiaries', 'domicile_kabkota_bps_id', $this->string());

        $this->addColumn('areas', 'code_bps_parent', $this->integer()->after('code_bps'));

        // Migrate new data
        \Yii::$app->db->createCommand('
                UPDATE areas SET code_bps_parent = CASE
                    WHEN LENGTH (code_bps) = 4 THEN SUBSTRING(code_bps FROM 1 FOR CHAR_LENGTH(code_bps) - 2)
                    WHEN LENGTH (code_bps) = 7 OR LENGTH (code_bps) = 10 THEN SUBSTRING(code_bps FROM 1 FOR CHAR_LENGTH(code_bps) - 3)
                END
        ')->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('beneficiaries', 'domicile_kel_bps_id', $this->integer());
        $this->alterColumn('beneficiaries', 'domicile_kec_bps_id', $this->integer());
        $this->alterColumn('beneficiaries', 'domicile_kabkota_bps_id', $this->integer());

        $this->dropColumn('areas', 'code_bps_parent');
    }
}
