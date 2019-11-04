<?php

use app\components\CustomMigration;

/**
 * Class m191031_033854_insert_rbac_news_important */
class m191031_033854_insert_rbac_news_important extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;

        // Manage permission
        $newsImportantManagePermission = $auth->createPermission('newsImportantManage');
        $newsImportantManagePermission->description = 'Get News Important Manage';
        $auth->add($newsImportantManagePermission);

        $role = $auth->getRole('admin');
        $auth->addChild($role, $newsImportantManagePermission);

        $role = $auth->getRole('staffProv');
        $auth->addChild($role, $newsImportantManagePermission);

        // List permission
        $newsImportantListPermission = $auth->createPermission('newsImportantList');
        $newsImportantListPermission->description = 'Get News Important List';
        $auth->add($newsImportantListPermission);

        $role = $auth->getRole('user');
        $auth->addChild($role, $newsImportantListPermission);

        $role = $auth->getRole('staffRW');
        $auth->addChild($role, $newsImportantListPermission);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $auth = Yii::$app->authManager;

        // Manage permission
        $newsImportantManagePermission = $auth->getPermission('newsImportantManage');
        $auth->remove($newsImportantManagePermission);

        $role = $auth->getRole('admin');
        $auth->removeChild($role, $newsImportantManagePermission);

        $role = $auth->getRole('staffprov');
        $auth->removeChild($role, $newsImportantManagePermission);

        // List permission
        $newsImportantListPermission = $auth->createPermission('newsImportantList');
        $newsImportantListPermission->description = 'Get News Important List';
        $auth->remove($newsImportantListPermission);

        $role = $auth->getRole('user');
        $auth->removeChild($role, $newsImportantListPermission);

        $role = $auth->getRole('staffRW');
        $auth->removeChild($role, $newsImportantListPermission);
    }
}
