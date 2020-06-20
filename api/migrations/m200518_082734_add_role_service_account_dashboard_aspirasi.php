<?php

use app\components\CustomMigration;

/**
 * Class m200518_082734_add_role_service_account_dashboard_aspirasi */
class m200518_082734_add_role_service_account_dashboard_aspirasi extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;

        // List permission
        $aspirasiWebadminViewPermission = $auth->getPermission('aspirasiWebadminView');
        // $auth->remove($aspirasiWebadminViewPermission);

        $role = $auth->getRole('service_account_dashboard');
        $auth->addChild($role, $aspirasiWebadminViewPermission);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = Yii::$app->authManager;

        // List permission
        $aspirasiWebadminViewPermission = $auth->getPermission('aspirasiWebadminView');
        // $auth->remove($aspirasiWebadminViewPermission);

        $role = $auth->getRole('service_account_dashboard');
        $auth->removeChild($role, $aspirasiWebadminViewPermission);
    }
}
