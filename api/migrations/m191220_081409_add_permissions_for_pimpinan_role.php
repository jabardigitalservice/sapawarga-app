<?php

use app\components\CustomMigration;

/**
 * Class m191220_081409_add_permissions_for_pimpinan_role */
class m191220_081409_add_permissions_for_pimpinan_role extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;
        $role = $auth->getRole('pimpinan');

        $permission = $auth->getPermission('pollingManage');
        $auth->addChild($role, $permission);
        $permission = $auth->getPermission('broadcastManage');
        $auth->addChild($role, $permission);
        $permission = $auth->getPermission('newsList');
        $auth->addChild($role, $permission);
        $permission = $auth->getPermission('newsSaberhoaxList');
        $auth->addChild($role, $permission);
        $permission = $auth->getPermission('aspirasiWebadminView');
        $auth->addChild($role, $permission);
        // Currently there are no specific permissions related to QnA
        // They are defined in controllers by assigning roles to actions
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = Yii::$app->authManager;
        $role = $auth->getRole('pimpinan');

        $permission = $auth->getPermission('pollingManage');
        $auth->removeChild($role, $permission);
        $permission = $auth->getPermission('broadcastManage');
        $auth->removeChild($role, $permission);
        $permission = $auth->getPermission('newsList');
        $auth->removeChild($role, $permission);
        $permission = $auth->getPermission('newsSaberhoaxList');
        $auth->removeChild($role, $permission);
        $permission = $auth->getPermission('aspirasiWebadminView');
        $auth->removeChild($role, $permission);
    }
}
