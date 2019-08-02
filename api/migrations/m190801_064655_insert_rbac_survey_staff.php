<?php

use app\components\CustomMigration;

/**
 * Class m190801_064655_insert_rbac_survey_staff */
class m190801_064655_insert_rbac_survey_staff extends CustomMigration
{
    private $_auth;

    private $_roleStaffProv;
    private $_roleStaffKabkota;

    private $_surveyManagePermission;
    private $_surveyListPermission;

    public function init()
    {
        $this->_auth = Yii::$app->authManager;

        $this->_roleStaffProv = $this->_auth->getRole('staffProv');
        $this->_roleStaffKabkota = $this->_auth->getRole('staffKabkota');

        $this->_surveyManagePermission = $this->_auth->getPermission('surveyManage');
        $this->_surveyListPermission = $this->_auth->getPermission('surveyList');

        parent::init();
    }

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->_auth->addChild($this->_roleStaffProv, $this->_surveyManagePermission);
        $this->_auth->addChild($this->_roleStaffKabkota, $this->_surveyListPermission);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->_auth->removeChild($this->_roleStaffKabkota, $this->_surveyListPermission);
        $this->_auth->removeChild($this->_roleStaffProv, $this->_surveyManagePermission);
    }
}
