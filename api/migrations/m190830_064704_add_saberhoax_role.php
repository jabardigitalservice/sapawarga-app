<?php

use app\components\CustomMigration;

/**
 * Class m190830_064704_add_saberhoax_role */
class m190830_064704_add_saberhoax_role extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;

        // Add saber hoax role
        $saberHoaxRole = $auth->createRole('staffSaberhoax');
        $saberHoaxRole->description = 'Staff Jabar Saber Hoaks';
        $auth->add($saberHoaxRole);

        // 'newsSaberHoaxList' permission
        $newsSaberHoaxListPermission = $auth->createPermission('newsSaberhoaxList');
        $newsSaberHoaxListPermission->description = 'Get News Saber Hoaks List';
        $auth->add($newsSaberHoaxListPermission);

        // 'newsSaberHoaxManage' permission
        $newsSaberHoaxManagePermission = $auth->createPermission('newsSaberhoaxManage');
        $newsSaberHoaxManagePermission->description = 'Manage News Saber Hoaks';
        $auth->add($newsSaberHoaxManagePermission);

        $rwRole = $auth->getRole('staffRW');
        $userRole = $auth->getRole('user');

        // Role assignment for 'newsSaberHoaxList' permission
        $auth->addChild($saberHoaxRole, $newsSaberHoaxListPermission);
        $auth->addChild($rwRole, $newsSaberHoaxListPermission);
        $auth->addChild($userRole, $newsSaberHoaxListPermission);

        // Role assignment for 'newsSaberHoaxManage' permission
        $auth->addChild($saberHoaxRole, $newsSaberHoaxManagePermission);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = Yii::$app->authManager;

        $saberHoaxRole = $auth->getRole('staffSaberhoax');
        $rwRole = $auth->getRole('staffRW');
        $userRole = $auth->getRole('user');

        $newsSaberHoaxListPermission = $auth->getPermission('newsSaberhoaxList');
        $newsSaberHoaxManagePermission = $auth->getPermission('newsSaberhoaxManage');

        // Remove assignment for 'newsSaberHoaxManage' permission
        $auth->removeChild($saberHoaxRole, $newsSaberHoaxManagePermission);

        // Remove assignment for 'newsSaberHoaxList' permission
        $auth->removeChild($userRole, $newsSaberHoaxListPermission);
        $auth->removeChild($rwRole, $newsSaberHoaxListPermission);
        $auth->removeChild($saberHoaxRole, $newsSaberHoaxListPermission);

        // Remove permissions
        $auth->remove($newsSaberHoaxManagePermission);
        $auth->remove($newsSaberHoaxListPermission);

        $auth->remove($saberHoaxRole);
    }
}
