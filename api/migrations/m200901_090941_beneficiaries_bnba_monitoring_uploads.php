<?php

use app\components\CustomMigration;
use app\models\BeneficiaryBnbaMonitoringUpload;

/**
 * Class m200901_090941_beneficiaries_bnba_monitoring_uploads */
class m200901_090941_beneficiaries_bnba_monitoring_uploads extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('beneficiaries_bnba_monitoring_uploads', [
            'id' => $this->primaryKey(),
            'code_bps' => $this->integer()->defaultValue(null),
            'kabkota_name' => $this->string(150)->defaultValue(null),
            'tahap_bantuan' => $this->integer(2)->defaultValue(null),
            'is_dtks' => $this->integer(1)->defaultValue(null),
            'last_updated' => $this->integer()->defaultValue(null),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        // Insert Data
        $this->insertDataMonitoringUpload();
    }

    public function insertDataMonitoringUpload()
    {
        $totalTahap = 4;
        $dtksType = ['dtks', 'non-dtks'];

        // Get all kabkota
        $rows = (new \yii\db\Query())
            ->select(['code_bps', 'name'])
            ->from('areas')
            ->where(['depth' => 2])
            ->all();

        // total insert data = $totalTahap x $rows x $dtksType
        for ($i=1; $i <= $totalTahap; $i++) {
            foreach ($rows as $key => $row) {
                foreach ($dtksType as $dtks) {
                    $data = new BeneficiaryBnbaMonitoringUpload();
                    $data->code_bps = $row['code_bps'];
                    $data->kabkota_name = $row['name'];
                    $data->tahap_bantuan = $i;
                    $data->is_dtks = ($dtks == 'dtks') ? 1 : 0 ;
                    $data->last_updated = '';
                    $data->save();
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('beneficiaries_bnba_monitoring_uploads');
    }
}
