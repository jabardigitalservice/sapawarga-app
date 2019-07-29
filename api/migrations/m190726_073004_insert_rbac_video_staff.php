<?php

use app\components\CustomMigration;

/**
 * Class m190726_073004_insert_rbac_video_staff */
class m190726_073004_insert_rbac_video_staff extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;

        // Manage permission
        $videoManagePermission = $auth->createPermission('videoManage');
        $videoManagePermission->description = 'Manage Video';
        $auth->add($videoManagePermission);

        $role = $auth->getRole('admin');
        $auth->addChild($role, $videoManagePermission);

        $role = $auth->getRole('staffProv');
        $auth->addChild($role, $videoManagePermission);

        $role = $auth->getRole('staffKabkota');
        $auth->addChild($role, $videoManagePermission);

        // List permission
        $videoListPermission = $auth->createPermission('videoList');
        $videoListPermission->description = 'Get Video List';
        $auth->add($videoListPermission);

        $role = $auth->getRole('user');
        $auth->addChild($role, $videoListPermission);

        $role = $auth->getRole('staffrw');
        $auth->addChild($role, $videoListPermission);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = Yii::$app->authManager;

        // Manage permission
        $videoManagePermission = $auth->createPermission('videoManage');
        $videoManagePermission->description = 'Manage Video';
        $auth->remove($videoManagePermission);

        $role = $auth->getRole('admin');
        $auth->removeChild($role, $videoManagePermission);

        $role = $auth->getRole('staffProv');
        $auth->removeChild($role, $videoManagePermission);

        $role = $auth->getRole('staffKabkota');
        $auth->removeChild($role, $videoManagePermission);

        // List permission
        $videoListPermission = $auth->createPermission('videoList');
        $videoListPermission->description = 'Get Video List';
        $auth->remove($videoListPermission);

        $role = $auth->getRole('user');
        $auth->removeChild($role, $videoListPermission);

        $role = $auth->getRole('staffrw');
        $auth->removeChild($role, $videoListPermission);
    }
}
