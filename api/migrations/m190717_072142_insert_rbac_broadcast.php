<?php

use app\components\CustomMigration;

/**
 * Class m190717_072142_insert_rbac_broadcast */
class m190717_072142_insert_rbac_broadcast extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;

        $broadcastManagePermission              = $auth->createPermission('broadcastManage');
        $broadcastManagePermission->description = 'Manage Broadcast';
        $auth->add($broadcastManagePermission);

        $role = $auth->getRole('admin');
        $auth->addChild($role, $broadcastManagePermission);

        $role = $auth->getRole('staffProv');
        $auth->addChild($role, $broadcastManagePermission);

        $role = $auth->getRole('staffKabkota');
        $auth->addChild($role, $broadcastManagePermission);

        $role = $auth->getRole('staffKec');
        $auth->addChild($role, $broadcastManagePermission);

        $role = $auth->getRole('staffKel');
        $auth->addChild($role, $broadcastManagePermission);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190717_072142_insert_rbac_broadcast cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190717_072142_insert_rbac_broadcast cannot be reverted.\n";

        return false;
    }
    */
}
