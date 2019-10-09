<?php

use app\components\CustomMigration;

/**
 * Class m191008_102316_add_trainer_role */
class m191008_102316_add_trainer_role extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;

        // Add saber hoax role
        $trainerRole = $auth->createRole('trainer');
        $trainerRole->description = 'Pelatih/PLD';
        $auth->add($trainerRole);

        $rwRole = $auth->getRole('staffRW');
        $auth->addChild($trainerRole, $rwRole);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = Yii::$app->authManager;

        $trainerRole = $auth->getRole('trainer');
        $rwRole = $auth->getRole('staffRW');
        $auth->removeChild($trainerRole, $rwRole);
        $auth->remove($trainerRole);
    }
}
