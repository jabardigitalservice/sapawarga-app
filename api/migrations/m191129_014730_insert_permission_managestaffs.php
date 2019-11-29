<?php

use app\components\CustomMigration;

/**
 * Class m191129_014730_insert_permission_managestaffs */
class m191129_014730_insert_permission_managestaffs extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;

        $permission = $auth->getPermission('manageStaffs');

        $role = $auth->getRole('admin');
        $auth->addChild($role, $permission);

        $role = $auth->getRole('staff');
        $auth->addChild($role, $permission);

        $role = $auth->getRole('staffKabkota');
        $auth->addChild($role, $permission);

        $role = $auth->getRole('staffKec');
        $auth->addChild($role, $permission);

        $role = $auth->getRole('staffKel');
        $auth->addChild($role, $permission);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m191129_014730_insert_permission_managestaffs cannot be reverted.\n";

        return false;
    }
}
