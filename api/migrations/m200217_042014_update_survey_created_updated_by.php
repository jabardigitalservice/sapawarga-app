<?php

use app\components\CustomMigration;

/**
 * Class m200217_042014_update_survey_created_updated_by */
class m200217_042014_update_survey_created_updated_by extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('survey', 'updated_by', $this->integer()->notNull()->after('status'));
        $this->addColumn('survey', 'created_by', $this->integer()->notNull()->after('status'));

        // Update existing data created_by updated_by by staffprov
        \Yii::$app->db->createCommand('UPDATE survey set created_by = 2, updated_by = 2;')
            ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('survey', 'created_by');
        $this->dropColumn('survey', 'updated_by');
    }
}
