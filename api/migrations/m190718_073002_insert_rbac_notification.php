<?php

use app\components\CustomMigration;

/**
 * Class m190718_073002_insert_rbac_notification */
class m190718_073002_insert_rbac_notification extends CustomMigration
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
        $notificationManagePermission = $this->_auth->createPermission('notificationManage');
        $notificationManagePermission->description = 'Manage Notification';
        $this->_auth->add($notificationManagePermission);

        $this->_auth->addChild($this->_roleAdmin, $notificationManagePermission);
        $this->_auth->addChild($this->_roleStaffProv, $notificationManagePermission);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $notificationManagePermission = $this->_auth->getPermission('notificationManage');

        $this->_auth->removeChild($this->_roleStaffProv, $notificationManagePermission);
        $this->_auth->removeChild($this->_roleAdmin, $notificationManagePermission);

        $this->_auth->remove($notificationManagePermission);
    }
}
