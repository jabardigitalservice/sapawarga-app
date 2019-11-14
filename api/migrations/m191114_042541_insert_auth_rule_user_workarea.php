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

        $permission = $auth->createPermission('updateStaffWithinWorkArea');
        $permission->description = 'Update Staff within his work area scopes';
        $permission->ruleName = $rule->name;
        $auth->add($permission);

        $role = $auth->getRole('staffProv');
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
        echo "m191114_042541_insert_auth_rule_user_workarea cannot be reverted.\n";

        return false;
    }
}
