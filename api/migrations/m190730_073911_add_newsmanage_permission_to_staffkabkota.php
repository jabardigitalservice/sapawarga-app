<?php

use app\components\CustomMigration;

/**
 * Class m190730_073911_add_newsmanage_permission_to_staffkabkota */
class m190730_073911_add_newsmanage_permission_to_staffkabkota extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;

        $newsManagePermission = $auth->getPermission('newsManage');
        $role = $auth->getRole('staffKabkota');
        $auth->addChild($role, $newsManagePermission);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = Yii::$app->authManager;

        $newsManagePermission = $auth->getPermission('newsManage');
        $role = $auth->getRole('staffKabkota');
        $auth->removeChild($role, $newsManagePermission);
    }
}
