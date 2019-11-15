<?php

use app\components\CustomMigration;
use Jdsteam\Sapawarga\Rbac\User\WorkAreaRule;

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

        $rule = new WorkAreaRule();
        $auth->add($rule);

        $editPermission = $auth->createPermission('edit_user');
        $editPermission->description = 'Edit User';
        $auth->add($editPermission);

        $editWorkAreaPermission = $auth->createPermission('edit_working-area_user');
        $editWorkAreaPermission->description = 'Edit User within his work area scopes';
        $editWorkAreaPermission->ruleName = $rule->name;

        $auth->add($editWorkAreaPermission);
        $auth->addChild($editWorkAreaPermission, $editPermission);

        $role = $auth->getRole('admin');
        $auth->addChild($role, $editWorkAreaPermission);

        $role = $auth->getRole('staff');
        $auth->addChild($role, $editWorkAreaPermission);

        $role = $auth->getRole('staffProv');
        $auth->addChild($role, $editWorkAreaPermission);

        $role = $auth->getRole('staffKabkota');
        $auth->addChild($role, $editWorkAreaPermission);

        $role = $auth->getRole('staffKec');
        $auth->addChild($role, $editWorkAreaPermission);

        $role = $auth->getRole('staffKel');
        $auth->addChild($role, $editWorkAreaPermission);
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
