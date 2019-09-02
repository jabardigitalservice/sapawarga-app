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
        $saberHoaxRole = $auth->createRole('saberHoax');
        $saberHoaxRole->description = 'Admin Jabar Saber Hoaks';
        $auth->add($saberHoaxRole);

        // 'newsSaberHoaxList' permission
        $newsSaberHoaxListPermission = $auth->createPermission('newsSaberHoaxList');
        $newsSaberHoaxListPermission->description = 'Get News Saber Hoaks List';
        $auth->add($newsSaberHoaxListPermission);

        // 'newsSaberHoaxManage' permission
        $newsSaberHoaxManagePermission = $auth->createPermission('newsSaberHoaxManage');
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

        $saberHoaxRole = $auth->getRole('saberHoax');
        $rwRole = $auth->getRole('staffRW');
        $userRole = $auth->getRole('user');

        $newsSaberHoaxListPermission = $auth->getPermission('newsSaberHoaxList');
        $newsSaberHoaxManagePermission = $auth->getPermission('newsSaberHoaxManage');

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
