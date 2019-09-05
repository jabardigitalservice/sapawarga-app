<?php

use app\components\CustomMigration;

/**
 * Class m190905_063305_insert_rbac_release */
class m190905_063305_insert_rbac_release extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;

        $releaseManagePermission = $auth->createPermission('releaseManage');
        $releaseManagePermission->description = 'Manage Release';
        $auth->add($releaseManagePermission);

        $role = $auth->getRole('admin');
        $auth->addChild($role, $releaseManagePermission);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = Yii::$app->authManager;
        $role = $auth->getRole('admin');
        $releaseManagePermission = $auth->getPermission('releaseManage');

        $auth->removeChild($role, $releaseManagePermission);

        $auth->remove($releaseManagePermission);
    }
}
