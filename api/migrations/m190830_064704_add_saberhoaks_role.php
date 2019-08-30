<?php

use app\components\CustomMigration;

/**
 * Class m190830_064704_add_saberhoaks_role */
class m190830_064704_add_saberhoaks_role extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;

        // Add saber hoaks role
        $saberHoaksRole = $auth->createRole('saberHoaks');
        $saberHoaksRole->description = 'Admin Jabar Saber Hoaks';
        $auth->add($saberHoaksRole);

        // 'newsSaberHoaksList' permission
        $newsSaberHoaksListPermission = $auth->createPermission('newsSaberHoaksList');
        $newsSaberHoaksListPermission->description = 'Get News Saber Hoaks List';
        $auth->add($newsSaberHoaksListPermission);

        // 'newsSaberHoaksManage' permission
        $newsSaberHoaksManagePermission = $auth->createPermission('newsSaberHoaksManage');
        $newsSaberHoaksManagePermission->description = 'Manage News Saber Hoaks';
        $auth->add($newsSaberHoaksManagePermission);

        $rwRole = $auth->getRole('staffRW');
        $userRole = $auth->getRole('user');

        // Role assignment for 'newsSaberHoaksList' permission
        $auth->addChild($saberHoaksRole, $newsSaberHoaksListPermission);
        $auth->addChild($rwRole, $newsSaberHoaksListPermission);
        $auth->addChild($userRole, $newsSaberHoaksListPermission);

        // Role assignment for 'newsSaberHoaksManage' permission
        $auth->addChild($saberHoaksRole, $newsSaberHoaksManagePermission);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = Yii::$app->authManager;

        $saberHoaksRole = $auth->getRole('saberHoaks');
        $rwRole = $auth->getRole('staffRW');
        $userRole = $auth->getRole('user');

        $newsSaberHoaksListPermission = $auth->getPermission('newsSaberHoaksList');
        $newsSaberHoaksManagePermission = $auth->getPermission('newsSaberHoaksManage');

        // Remove assignment for 'newsSaberHoaksManage' permission
        $auth->removeChild($saberHoaksRole, $newsSaberHoaksManagePermission);

        // Remove assignment for 'newsSaberHoaksList' permission
        $auth->removeChild($userRole, $newsSaberHoaksListPermission);
        $auth->removeChild($rwRole, $newsSaberHoaksListPermission);
        $auth->removeChild($saberHoaksRole, $newsSaberHoaksListPermission);

        // Remove permissions
        $auth->remove($newsSaberHoaksManagePermission);
        $auth->remove($newsSaberHoaksListPermission);

        $auth->remove($saberHoaksRole);
    }
}
