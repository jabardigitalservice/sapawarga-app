<?php

use app\components\CustomMigration;

/**
 * Class m200120_095604_create_table_gamifications_participant_and_activity */
class m200120_095604_create_table_gamifications_participant_and_activity extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('gamification_activities', [
            'id' => $this->primaryKey(),
            'gamification_id' => $this->integer()->notNull(),
            'object_id' => $this->integer()->notNull(), // id of news / user_post
            'user_id' => $this->integer()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->createTable('gamification_participants', [
            'id' => $this->primaryKey(),
            'gamification_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'total_user_hit' => $this->integer()->notNull()->defaultValue(0),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-gamification_activities-gamification_id',
            'gamification_activities',
            'gamification_id',
            'gamifications',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-gamification_participants-gamification_id',
            'gamification_participants',
            'gamification_id',
            'gamifications',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-gamification_activities-gamification_id', 'gamification_activities');
        $this->dropForeignKey('fk-gamification_participants-gamification_id', 'gamification_participants');

        $this->dropTable('gamification_activities');
        $this->dropTable('gamification_participants');
    }
}
