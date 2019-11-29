<?php

use app\components\CustomMigration;

/**
 * Class m191129_022704_insert_permission_managestaffs_staffprov */
class m191129_022704_insert_permission_managestaffs_staffprov extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;

        $permission = $auth->getPermission('manageStaffs');

        $role = $auth->getRole('staffProv');
        $auth->addChild($role, $permission);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m191129_022704_insert_permission_managestaffs_staffprov cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m191129_022704_insert_permission_managestaffs_staffprov cannot be reverted.\n";

        return false;
    }
    */
}
