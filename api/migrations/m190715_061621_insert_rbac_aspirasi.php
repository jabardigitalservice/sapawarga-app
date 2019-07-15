<?php

use app\components\CustomMigration;

/**
 * Class m190715_061621_insert_rbac_aspirasi */
class m190715_061621_insert_rbac_aspirasi extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;

        $aspirasiMobilePermission              = $auth->createPermission('aspirasiMobile');
        $aspirasiMobilePermission->description = 'View Published and My Aspirasi. Create, Update and Delete My Aspirasi Draft. Give Likes to Published Aspirasi.';
        $auth->add($aspirasiMobilePermission);

        $aspirasiWebadminViewPermission              = $auth->createPermission('aspirasiWebadminView');
        $aspirasiWebadminViewPermission->description = 'View Pending, Rejected, and Published Aspirasi.';
        $auth->add($aspirasiWebadminViewPermission);

        $aspirasiWebadminManagePermission              = $auth->createPermission('aspirasiWebadminManage');
        $aspirasiWebadminManagePermission->description = 'Manage Aspirasi (Full privileges)';
        $auth->add($aspirasiWebadminManagePermission);

        $role = $auth->getRole('admin');
        $auth->addChild($role, $aspirasiWebadminManagePermission);

        $role = $auth->getRole('staffProv');
        $auth->addChild($role, $aspirasiWebadminManagePermission);

        $role = $auth->getRole('staffKabkota');
        $auth->addChild($role, $aspirasiWebadminViewPermission);

        $role = $auth->getRole('staffKec');
        $auth->addChild($role, $aspirasiWebadminViewPermission);

        $role = $auth->getRole('staffKel');
        $auth->addChild($role, $aspirasiWebadminViewPermission);

        $role = $auth->getRole('staffRW');
        $auth->addChild($role, $aspirasiMobilePermission);

        $role = $auth->getRole('user');
        $auth->addChild($role, $aspirasiMobilePermission);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = Yii::$app->authManager;
        $aspirasiMobilePermission           = $auth->getPermission('aspirasiMobile');
        $aspirasiWebadminViewPermission     = $auth->getPermission('aspirasiWebadminView');
        $aspirasiWebadminManagePermission   = $auth->getPermission('aspirasiWebadminManage');

        $role = $auth->getRole('user');
        $auth->removeChild($role, $aspirasiMobilePermission); 
        
        $role = $auth->getRole('staffRW');
        $auth->removeChild($role, $aspirasiMobilePermission);

        $role = $auth->getRole('staffKel');
        $auth->removeChild($role, $aspirasiWebadminViewPermission);

        $role = $auth->getRole('staffKec');
        $auth->removeChild($role, $aspirasiWebadminViewPermission);

        $role = $auth->getRole('staffKabkota');
        $auth->removeChild($role, $aspirasiWebadminViewPermission);

        $role = $auth->getRole('staffProv');
        $auth->removeChild($role, $aspirasiWebadminManagePermission);

        $role = $auth->getRole('admin');
        $auth->removeChild($role, $aspirasiWebadminManagePermission);

        $auth->remove($aspirasiWebadminManagePermission);
        $auth->remove($aspirasiWebadminViewPermission);           
        $auth->remove($aspirasiMobilePermission);
    }
}
