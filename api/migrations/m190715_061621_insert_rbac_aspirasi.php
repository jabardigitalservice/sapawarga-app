<?php

use app\components\CustomMigration;

/**
 * Class m190715_061621_insert_rbac_aspirasi */
class m190715_061621_insert_rbac_aspirasi extends CustomMigration
{
    private $auth;

    private $roleAdmin;
    private $roleStaffProv;
    private $roleStaffKabkota;
    private $roleStaffKec;
    private $roleStaffKel;
    private $roleStaffRW;
    private $roleUser;

    public function init()
    {
        $this->auth = Yii::$app->authManager;

        $this->roleAdmin = $this->auth->getRole('admin');
        $this->roleStaffProv = $this->auth->getRole('staffProv');
        $this->roleStaffKabkota = $this->auth->getRole('staffKabkota');
        $this->roleStaffKec = $this->auth->getRole('staffKec');
        $this->roleStaffKel = $this->auth->getRole('staffKel');
        $this->roleStaffRW = $this->auth->getRole('staffRW');
        $this->roleUser = $this->auth->getRole('user');

        parent::init();
    }

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

        $auth->addChild($this->roleAdmin, $aspirasiWebadminManagePermission);
        $auth->addChild($this->roleStaffProv, $aspirasiWebadminManagePermission);

        $auth->addChild($this->roleStaffKabkota, $aspirasiWebadminViewPermission);
        $auth->addChild($this->roleStaffKec, $aspirasiWebadminViewPermission);
        $auth->addChild($this->roleStaffKel, $aspirasiWebadminViewPermission);

        $auth->addChild($this->roleStaffRW, $aspirasiMobilePermission);
        $auth->addChild($this->roleUser, $aspirasiMobilePermission);

        // $this->removeStaffChildRole($auth);
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

        $auth->removeChild($this->roleUser, $aspirasiMobilePermission); 
        $auth->removeChild($this->roleStaffRW, $aspirasiMobilePermission);

        $auth->removeChild($this->roleStaffKel, $aspirasiWebadminViewPermission);
        $auth->removeChild($this->roleStaffKec, $aspirasiWebadminViewPermission);
        $auth->removeChild($this->roleStaffKabkota, $aspirasiWebadminViewPermission);
        
        $auth->removeChild($this->roleStaffProv, $aspirasiWebadminManagePermission);
        $auth->removeChild($this->roleAdmin, $aspirasiWebadminManagePermission);

        $auth->remove($aspirasiWebadminManagePermission);
        $auth->remove($aspirasiWebadminViewPermission);           
        $auth->remove($aspirasiMobilePermission);
    }

    private function removeStaffChildRole($auth)
    {
        // $auth->addChild($staffKel, $staffRW);
    }
    
}
