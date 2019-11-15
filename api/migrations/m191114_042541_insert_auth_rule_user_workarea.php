<?php

use app\components\CustomMigration;
use Jdsteam\Sapawarga\Rbac\User\CascadedUserRule;

/**
 * Class m191114_042541_insert_auth_rule_user_workarea */
class m191114_042541_insert_auth_rule_user_workarea extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;

        $rule = new CascadedUserRule();
        $auth->add($rule);

        // CREATE PERMISSION
        $createPermission = $auth->createPermission('create_user');
        $createPermission->description = 'Create User';
        $auth->add($createPermission);

        $role = $auth->getRole('admin');
        $auth->addChild($role, $createPermission);

        $role = $auth->getRole('staff');
        $auth->addChild($role, $createPermission);

        $role = $auth->getRole('staffProv');
        $auth->addChild($role, $createPermission);

        $role = $auth->getRole('staffKabkota');
        $auth->addChild($role, $createPermission);

        $role = $auth->getRole('staffKec');
        $auth->addChild($role, $createPermission);

        $role = $auth->getRole('staffKel');
        $auth->addChild($role, $createPermission);



        // VIEW PERMISSION
        $viewPermission = $auth->createPermission('view_user');
        $viewPermission->description = 'View User';
        $auth->add($viewPermission);

        $viewCascadedPermission = $auth->createPermission('view_cascaded_user');
        $viewCascadedPermission->description = 'View User within his work area scopes';
        $viewCascadedPermission->ruleName = $rule->name;

        $auth->add($viewCascadedPermission);
        $auth->addChild($viewCascadedPermission, $viewPermission);

        $role = $auth->getRole('admin');
        $auth->addChild($role, $viewPermission);

        $role = $auth->getRole('staff');
        $auth->addChild($role, $viewCascadedPermission);

        $role = $auth->getRole('staffProv');
        $auth->addChild($role, $viewCascadedPermission);

        $role = $auth->getRole('staffKabkota');
        $auth->addChild($role, $viewCascadedPermission);

        $role = $auth->getRole('staffKec');
        $auth->addChild($role, $viewCascadedPermission);

        $role = $auth->getRole('staffKel');
        $auth->addChild($role, $viewCascadedPermission);



        // EDIT PERMISSION
        $editPermission = $auth->createPermission('edit_user');
        $editPermission->description = 'Edit User';
        $auth->add($editPermission);

        $editCascadedPermission = $auth->createPermission('edit_cascaded_user');
        $editCascadedPermission->description = 'Edit User within his work area scopes';
        $editCascadedPermission->ruleName = $rule->name;

        $auth->add($editCascadedPermission);
        $auth->addChild($editCascadedPermission, $editPermission);

        $role = $auth->getRole('admin');
        $auth->addChild($role, $editPermission);

        $role = $auth->getRole('staff');
        $auth->addChild($role, $editCascadedPermission);

        $role = $auth->getRole('staffProv');
        $auth->addChild($role, $editCascadedPermission);

        $role = $auth->getRole('staffKabkota');
        $auth->addChild($role, $editCascadedPermission);

        $role = $auth->getRole('staffKec');
        $auth->addChild($role, $editCascadedPermission);

        $role = $auth->getRole('staffKel');
        $auth->addChild($role, $editCascadedPermission);



        // DELETE PERMISSION
        $deletePermission = $auth->createPermission('delete_user');
        $deletePermission->description = 'Delete User';
        $auth->add($deletePermission);

        $deleteCascadedPermission = $auth->createPermission('delete_cascaded_user');
        $deleteCascadedPermission->description = 'Edit User within his work area scopes';
        $deleteCascadedPermission->ruleName = $rule->name;

        $auth->add($deleteCascadedPermission);
        $auth->addChild($deleteCascadedPermission, $deletePermission);

        $role = $auth->getRole('admin');
        $auth->addChild($role, $deletePermission);

        $role = $auth->getRole('staff');
        $auth->addChild($role, $deleteCascadedPermission);

        $role = $auth->getRole('staffProv');
        $auth->addChild($role, $deleteCascadedPermission);

        $role = $auth->getRole('staffKabkota');
        $auth->addChild($role, $deleteCascadedPermission);

        $role = $auth->getRole('staffKec');
        $auth->addChild($role, $deleteCascadedPermission);

        $role = $auth->getRole('staffKel');
        $auth->addChild($role, $deleteCascadedPermission);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m191114_042541_insert_auth_rule_user_workarea cannot be reverted.\n";

        return false;
    }
}
