<?php

use app\components\CustomMigration;

/**
 * Class m191104_075234_add_user_birthdate */
class m191104_075234_add_user_birthdate extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('user', 'birth_date', $this->date()->null()->after('education_level_id'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('user', 'birth_date');
    }
}
