<?php

use app\components\CustomMigration;

/**
 * Class m191016_134330_insert_rbac_popups */
class m191016_134330_insert_rbac_popups extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;

        // Manage permission
        $popupManagePermission = $auth->createPermission('popupManage');
        $popupManagePermission->description = 'Get Popup Manage';
        $auth->add($popupManagePermission);

        $role = $auth->getRole('admin');
        $auth->addChild($role, $popupManagePermission);

        $role = $auth->getRole('staffProv');
        $auth->addChild($role, $popupManagePermission);

        // List permission
        $popupListPermission = $auth->createPermission('popupList');
        $popupListPermission->description = 'Get Popup List';
        $auth->add($popupListPermission);

        $role = $auth->getRole('user');
        $auth->addChild($role, $popupListPermission);

        $role = $auth->getRole('staffRW');
        $auth->addChild($role, $popupListPermission);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = Yii::$app->authManager;

        // Manage permission
        $popupManagePermission = $auth->getPermission('popupManage');
        $auth->remove($popupManagePermission);

        $role = $auth->getRole('admin');
        $auth->removeChild($role, $popupManagePermission);

        $role = $auth->getRole('staffprov');
        $auth->removeChild($role, $popupManagePermission);

        // List permission
        $popupListPermission = $auth->createPermission('popupList');
        $popupListPermission->description = 'Get Popup List';
        $auth->remove($popupListPermission);

        $role = $auth->getRole('user');
        $auth->removeChild($role, $popupListPermission);

        $role = $auth->getRole('staffRW');
        $auth->removeChild($role, $popupListPermission);
    }
}
