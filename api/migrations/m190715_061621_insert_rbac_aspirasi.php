<?php

use app\components\CustomMigration;

/**
 * Class m190715_061621_insert_rbac_aspirasi */
class m190715_061621_insert_rbac_aspirasi extends CustomMigration
{
    private $_auth;

    private $_roleAdmin;
    private $_roleStaffProv;
    private $_roleStaffKabkota;
    private $_roleStaffKec;
    private $_roleStaffKel;
    private $_roleStaffRW;
    private $_roleUser;

    public function init()
    {
        $this->_auth = Yii::$app->authManager;

        $this->_roleAdmin = $this->_auth->getRole('admin');
        $this->_roleStaffProv = $this->_auth->getRole('staffProv');
        $this->_roleStaffKabkota = $this->_auth->getRole('staffKabkota');
        $this->_roleStaffKec = $this->_auth->getRole('staffKec');
        $this->_roleStaffKel = $this->_auth->getRole('staffKel');
        $this->_roleStaffRW = $this->_auth->getRole('staffRW');
        $this->_roleUser = $this->_auth->getRole('user');

        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $aspirasiMobilePermission              = $this->_auth->createPermission('aspirasiMobile');
        $aspirasiMobilePermission->description = 'View Published and My Aspirasi. Create, Update and Delete My Aspirasi Draft. Give Likes to Published Aspirasi.';
        $this->_auth->add($aspirasiMobilePermission);

        $aspirasiWebadminViewPermission              = $this->_auth->createPermission('aspirasiWebadminView');
        $aspirasiWebadminViewPermission->description = 'View Pending, Rejected, and Published Aspirasi.';
        $this->_auth->add($aspirasiWebadminViewPermission);

        $aspirasiWebadminManagePermission              = $this->_auth->createPermission('aspirasiWebadminManage');
        $aspirasiWebadminManagePermission->description = 'Manage Aspirasi (Full privileges)';
        $this->_auth->add($aspirasiWebadminManagePermission);

        $this->_auth->addChild($this->_roleAdmin, $aspirasiWebadminManagePermission);
        $this->_auth->addChild($this->_roleStaffProv, $aspirasiWebadminManagePermission);

        $this->_auth->addChild($this->_roleStaffKabkota, $aspirasiWebadminViewPermission);
        $this->_auth->addChild($this->_roleStaffKec, $aspirasiWebadminViewPermission);
        $this->_auth->addChild($this->_roleStaffKel, $aspirasiWebadminViewPermission);

        $this->_auth->addChild($this->_roleStaffRW, $aspirasiMobilePermission);
        $this->_auth->addChild($this->_roleUser, $aspirasiMobilePermission);

        $this->removeStaffChildRole($this->_auth);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addStaffChildRole($this->_auth);

        $aspirasiMobilePermission           = $this->_auth->getPermission('aspirasiMobile');
        $aspirasiWebadminViewPermission     = $this->_auth->getPermission('aspirasiWebadminView');
        $aspirasiWebadminManagePermission   = $this->_auth->getPermission('aspirasiWebadminManage');

        $this->_auth->removeChild($this->_roleUser, $aspirasiMobilePermission);
        $this->_auth->removeChild($this->_roleStaffRW, $aspirasiMobilePermission);

        $this->_auth->removeChild($this->_roleStaffKel, $aspirasiWebadminViewPermission);
        $this->_auth->removeChild($this->_roleStaffKec, $aspirasiWebadminViewPermission);
        $this->_auth->removeChild($this->_roleStaffKabkota, $aspirasiWebadminViewPermission);
        
        $this->_auth->removeChild($this->_roleStaffProv, $aspirasiWebadminManagePermission);
        $this->_auth->removeChild($this->_roleAdmin, $aspirasiWebadminManagePermission);

        $this->_auth->remove($aspirasiWebadminManagePermission);
        $this->_auth->remove($aspirasiWebadminViewPermission);
        $this->_auth->remove($aspirasiMobilePermission);
    }

    private function removeStaffChildRole($auth)
    {
        $auth->removeChild($this->_roleStaffProv, $this->_roleStaffKabkota);
        $auth->removeChild($this->_roleStaffKabkota, $this->_roleStaffKec);
        $auth->removeChild($this->_roleStaffKec, $this->_roleStaffKel);
        $auth->removeChild($this->_roleStaffKel, $this->_roleStaffRW);
    }

    private function addStaffChildRole($auth)
    {
        $auth->addChild($this->_roleStaffKel, $this->_roleStaffRW);
        $auth->addChild($this->_roleStaffKec, $this->_roleStaffKel);
        $auth->addChild($this->_roleStaffKabkota, $this->_roleStaffKec);
        $auth->addChild($this->_roleStaffProv, $this->_roleStaffKabkota);
    }
}
