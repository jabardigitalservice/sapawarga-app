<?php

use app\components\CustomMigration;

/**
 * Class m200130_090051_update_rbac_staffOPD */
class m200130_090051_update_rbac_staffOPD extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;
        $role = $auth->getRole('staffOPD');

        $permission = $auth->getPermission('broadcastManage');
        $auth->addChild($role, $permission);
        $permission = $auth->getPermission('pollingManage');
        $auth->addChild($role, $permission);
        $permission = $auth->getPermission('surveyManage');
        $auth->addChild($role, $permission);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = Yii::$app->authManager;
        $role = $auth->getRole('staffOPD');

        $permission = $auth->getPermission('surveyManage');
        $auth->removeChild($role, $permission);
        $permission = $auth->getPermission('pollingManage');
        $auth->removeChild($role, $permission);
        $permission = $auth->getPermission('broadcastManage');
        $auth->removeChild($role, $permission);
    }
}
