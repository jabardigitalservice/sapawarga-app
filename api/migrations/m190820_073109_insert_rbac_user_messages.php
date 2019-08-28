<?php

use app\components\CustomMigration;

/**
 * Class m190820_073109_insert_rbac_user_messages */
class m190820_073109_insert_rbac_user_messages extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;

        // List permission
        $userMessageListPermission = $auth->createPermission('userMessageList');
        $userMessageListPermission->description = 'Get User Message List';
        $auth->add($userMessageListPermission);

        $role = $auth->getRole('user');
        $auth->addChild($role, $userMessageListPermission);

        $role = $auth->getRole('staffRW');
        $auth->addChild($role, $userMessageListPermission);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = Yii::$app->authManager;

        // List permission
        $userMessageListPermission = $auth->createPermission('userMessageList');
        $userMessageListPermission->description = 'Get User Message List';
        $auth->remove($userMessageListPermission);

        $role = $auth->getRole('user');
        $auth->removeChild($role, $userMessageListPermission);

        $role = $auth->getRole('staffRW');
        $auth->removeChild($role, $userMessageListPermission);
    }
}
