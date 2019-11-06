<?php

use app\components\CustomMigration;

/**
 * Class m191106_035547_insert_job_types_ref */
class m191106_035547_insert_job_types_ref extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $now = time();

        $titles = [
            'TNI/Polisi', 'Wiraswasta', 'Petani', 'Peternak',
            'Nelayan', 'Tukang Bangunan', 'Pengobatan', 'Hukum', 'Tokoh Agama', 'Pemerintahan',
            'Pendidikan', 'Kesehatan', 'Keuangan', 'Mesin',
        ];

        foreach ($titles as $title) {
            $this->insert('job_types', [
                'title'      => $title,
                'seq'        => 1,
                'status'     => 10,
                'created_by' => 1,
                'created_at' => $now,
                'updated_by' => 1,
                'updated_at' => $now,
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m191106_035547_insert_job_types_ref cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191106_035547_insert_job_types_ref cannot be reverted.\n";

        return false;
    }
    */
}
