<?php

use app\components\CustomMigration;

/**
 * Class m191126_043906_insert_rbac_kabkota_dashboard */
class m191126_043906_insert_rbac_kabkota_dashboard extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;

        $role = $auth->getRole('staffKabkota');

        $dashboardListPermission = $auth->getPermission('dashboardList');
        $auth->addChild($role, $dashboardListPermission);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = Yii::$app->authManager;

        $role = $auth->getRole('staffKabkota');

        $dashboardListPermission = $auth->getPermission('dashboardList');
        $auth->removeChild($role, $dashboardListPermission);
    }
}
