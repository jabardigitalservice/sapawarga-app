<?php

use app\components\CustomMigration;

/**
 * Class m191226_094809_add_permissions_for_pimpinan_role_and_remove_areaList_permission */
class m191226_094809_add_permissions_for_pimpinan_role_and_remove_areaList_permission extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;

        // add permissions to pimpinan role
        $role = $auth->getRole('pimpinan');
        $permission = $auth->getPermission('surveyList');
        $auth->addChild($role, $permission);

        // remove areaList permission
        $permission = $auth->getPermission('areaList');

        $role = $auth->getRole('admin');
        $auth->removeChild($role, $permission);
        $role = $auth->getRole('staff');
        $auth->removeChild($role, $permission);
        $role = $auth->getRole('staffProv');
        $auth->removeChild($role, $permission);
        $role = $auth->getRole('staffKabkota');
        $auth->removeChild($role, $permission);
        $role = $auth->getRole('staffKec');
        $auth->removeChild($role, $permission);
        $role = $auth->getRole('staffKel');
        $auth->removeChild($role, $permission);
        $role = $auth->getRole('staffRW');
        $auth->removeChild($role, $permission);
        $role = $auth->getRole('user');
        $auth->removeChild($role, $permission);

        $auth->remove($permission);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = Yii::$app->authManager;

        // add areaList permission
        $permission              = $auth->createPermission('areaList');
        $permission->description = 'Get Area List';
        $auth->add($permission);

        $role = $auth->getRole('user');
        $auth->addChild($role, $permission);
        $role = $auth->getRole('staffRW');
        $auth->addChild($role, $permission);
        $role = $auth->getRole('staffKel');
        $auth->addChild($role, $permission);
        $role = $auth->getRole('staffKec');
        $auth->addChild($role, $permission);
        $role = $auth->getRole('staffKabkota');
        $auth->addChild($role, $permission);
        $role = $auth->getRole('staffProv');
        $auth->addChild($role, $permission);
        $role = $auth->getRole('staff');
        $auth->addChild($role, $permission);
        $role = $auth->getRole('admin');
        $auth->addChild($role, $permission);

        // remove permissions to pimpinan role
        $role = $auth->getRole('pimpinan');
        $permission = $auth->getPermission('surveyList');
        $auth->removeChild($role, $permission);
    }
}
