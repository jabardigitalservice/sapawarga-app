<?php

use app\components\CustomMigration;

/**
 * Class m191216_072050_insert_rbac_survey_staffkabkota */
class m191216_072050_insert_rbac_survey_staffkabkota extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;
        $roleStaffKabkota = $auth->getRole('staffKabkota');
        $surveyListPermission = $auth->getPermission('surveyList');
        $surveyManagePermission = $auth->getPermission('surveyManage');

        $auth->removeChild($roleStaffKabkota, $surveyListPermission);
        $auth->addChild($roleStaffKabkota, $surveyManagePermission);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = Yii::$app->authManager;
        $roleStaffKabkota = $auth->getRole('staffKabkota');
        $surveyListPermission = $auth->getPermission('surveyList');
        $surveyManagePermission = $auth->getPermission('surveyManage');

        $auth->removeChild($roleStaffKabkota, $surveyManagePermission);
        $auth->addChild($roleStaffKabkota, $surveyListPermission);
    }
}
