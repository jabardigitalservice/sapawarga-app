<?php

use app\components\CustomMigration;

/**
 * Class m190722_033754_insert_rbac_polling_staff */
class m190722_033754_insert_rbac_polling_staff extends CustomMigration
{
    private $_auth;

    private $_roleStaffProv;
    private $_roleStaffKabkota;
    private $_roleStaffKec;
    private $_roleStaffKel;

    private $_pollingManagePermission;

    public function init()
    {
        $this->_auth = Yii::$app->authManager;

        $this->_roleStaffProv = $this->_auth->getRole('staffProv');
        $this->_roleStaffKabkota = $this->_auth->getRole('staffKabkota');
        $this->_roleStaffKec = $this->_auth->getRole('staffKec');
        $this->_roleStaffKel = $this->_auth->getRole('staffKel');

        $this->_pollingManagePermission = $this->_auth->getPermission('pollingManage');

        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->_auth->addChild($this->_roleStaffProv, $this->_pollingManagePermission);
        $this->_auth->addChild($this->_roleStaffKabkota, $this->_pollingManagePermission);
        $this->_auth->addChild($this->_roleStaffKec, $this->_pollingManagePermission);
        $this->_auth->addChild($this->_roleStaffKel, $this->_pollingManagePermission);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->_auth->removeChild($this->_roleStaffKel, $this->_pollingManagePermission);
        $this->_auth->removeChild($this->_roleStaffKec, $this->_pollingManagePermission);
        $this->_auth->removeChild($this->_roleStaffKabkota, $this->_pollingManagePermission);
        $this->_auth->removeChild($this->_roleStaffProv, $this->_pollingManagePermission);
    }
}
