<?php

use app\components\CustomMigration;

/**
 * Class m191218_042345_add_opd_pimpinan_role */
class m191218_042345_add_opd_pimpinan_role extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;

        // Add OPD role
        $role = $auth->createRole('staffOPD');
        $role->description = 'Staff Organisasi Perangkat Daerah';
        $auth->add($role);
        $permission = $auth->getPermission('newsImportantManage');
        $auth->addChild($role, $permission);

        // Add Pimpinan/Gubernur role
        $role = $auth->createRole('pimpinan');
        $role->description = 'Pimpinan/Gubernur';
        $auth->add($role);
        $permission = $auth->getPermission('dashboardList');
        $auth->addChild($role, $permission);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = Yii::$app->authManager;

        // Remove Pimpinan/Gubernur role
        $role = $auth->getRole('pimpinan');
        $permission = $auth->getPermission('dashboardList');
        $auth->removeChild($role, $permission);
        $auth->remove($role);

        // Remove OPD role
        $role = $auth->getRole('staffOPD');
        $permission = $auth->getPermission('newsImportantManage');
        $auth->removeChild($role, $permission);
        $auth->remove($role);
    }
}
