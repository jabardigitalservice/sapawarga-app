<?php

use app\components\CustomMigration;

/**
 * Class m191213_042319_insert_rbac_api_service_account */
class m191213_042319_insert_rbac_api_service_account extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        $auth = Yii::$app->authManager;

        $serviceAccountRole = $auth->createRole('service_account_dashboard');
        $serviceAccountRole->description = 'Service Account Dashboard';
        $auth->add($serviceAccountRole);

        $dashboardPermission = $auth->getPermission('dashboardList');
        $auth->addChild($serviceAccountRole, $dashboardPermission);
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        echo "m191213_042319_insert_rbac_api_service_account cannot be reverted.\n";

        return false;
    }
}
