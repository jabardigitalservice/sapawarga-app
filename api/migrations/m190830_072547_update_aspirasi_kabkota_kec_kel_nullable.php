<?php

use app\components\CustomMigration;

/**
 * Class m190830_072547_update_aspirasi_kabkota_kec_kel_nullable */
class m190830_072547_update_aspirasi_kabkota_kec_kel_nullable extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('aspirasi', 'kabkota_id', $this->integer()->notNull());
        $this->alterColumn('aspirasi', 'kec_id', $this->integer()->notNull());
        $this->alterColumn('aspirasi', 'kel_id', $this->integer()->notNull());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->alterColumn('aspirasi', 'kabkota_id', $this->integer()->null());
        $this->alterColumn('aspirasi', 'kec_id', $this->integer()->null());
        $this->alterColumn('aspirasi', 'kel_id', $this->integer()->null());
    }
}
