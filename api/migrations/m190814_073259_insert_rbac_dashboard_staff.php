<?php

use app\components\CustomMigration;

/**
 * Class m190814_073259_insert_rbac_dashboard_staff */
class m190814_073259_insert_rbac_dashboard_staff extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;

        // List permission
        $dashboardListPermission = $auth->createPermission('dashboardList');
        $dashboardListPermission->description = 'Get Dashboard List';
        $auth->add($dashboardListPermission);

        $role = $auth->getRole('admin');
        $auth->addChild($role, $dashboardListPermission);

        $role = $auth->getRole('staffProv');
        $auth->addChild($role, $dashboardListPermission);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = Yii::$app->authManager;

        // List permission
        $dashboardListPermission = $auth->createPermission('dashboardList');
        $dashboardListPermission->description = 'Get Dashboard List';
        $auth->remove($dashboardListPermission);

        $role = $auth->getRole('admin');
        $auth->removeChild($role, $dashboardListPermission);

        $role = $auth->getRole('staffprov');
        $auth->removeChild($role, $dashboardListPermission);
    }
}
