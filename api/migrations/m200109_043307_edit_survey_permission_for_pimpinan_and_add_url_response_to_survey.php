<?php

use app\components\CustomMigration;

/**
 * Class m200109_043307_edit_survey_permission_for_pimpinan_and_add_url_response_to_survey */
class m200109_043307_edit_survey_permission_for_pimpinan_and_add_url_response_to_survey extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;

        // add surveyManage and remove surveyList permissions from pimpinan role
        $role = $auth->getRole('pimpinan');
        $permission = $auth->getPermission('surveyManage');
        $auth->addChild($role, $permission);

        $permission = $auth->getPermission('surveyList');
        $auth->removeChild($role, $permission);

        // add 'response_url' column to 'survey' table
        $this->addColumn('survey', 'response_url', $this->string()->null()->after('external_url'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // remove 'response_url' column from 'survey' table
        $this->dropColumn('survey', 'response_url');

        $auth = Yii::$app->authManager;

        // add surveyList and remove surveyManage permissions from pimpinan role
        $role = $auth->getRole('pimpinan');
        $permission = $auth->getPermission('surveyList');
        $auth->addChild($role, $permission);

        $permission = $auth->getPermission('surveyManage');
        $auth->removeChild($role, $permission);
    }
}
