<?php

use app\components\CustomMigration;

/**
 * Class m190929_114525_insert_rbac_banners */
class m190929_114525_insert_rbac_banners extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;

        // Manage permission
        $bannerManagePermission = $auth->createPermission('bannerManage');
        $bannerManagePermission->description = 'Get Banner Manage';
        $auth->add($bannerManagePermission);

        $role = $auth->getRole('admin');
        $auth->addChild($role, $bannerManagePermission);

        $role = $auth->getRole('staffProv');
        $auth->addChild($role, $bannerManagePermission);

        // List permission
        $bannerListPermission = $auth->createPermission('bannerList');
        $bannerListPermission->description = 'Get Banner List';
        $auth->add($bannerListPermission);

        $role = $auth->getRole('user');
        $auth->addChild($role, $bannerListPermission);

        $role = $auth->getRole('staffRW');
        $auth->addChild($role, $bannerListPermission);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = Yii::$app->authManager;

        // Manage permission
        $bannerManagePermission = $auth->getPermission('bannerManage');
        $auth->remove($bannerManagePermission);

        $role = $auth->getRole('admin');
        $auth->removeChild($role, $bannerManagePermission);

        $role = $auth->getRole('staffprov');
        $auth->removeChild($role, $bannerManagePermission);

        // List permission
        $bannerListPermission = $auth->createPermission('bannerList');
        $bannerListPermission->description = 'Get Banner List';
        $auth->remove($bannerListPermission);

        $role = $auth->getRole('user');
        $auth->removeChild($role, $bannerListPermission);

        $role = $auth->getRole('staffRW');
        $auth->removeChild($role, $bannerListPermission);
    }
}
