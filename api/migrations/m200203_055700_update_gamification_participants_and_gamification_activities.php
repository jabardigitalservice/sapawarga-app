<?php

use app\components\CustomMigration;

/**
 * Class m200203_055700_update_gamification_participants_and_gamification_activities */
class m200203_055700_update_gamification_participants_and_gamification_activities extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('gamification_activities', 'object_type');
        $this->addColumn('gamification_participants', 'total_user_hit', $this->integer()->notNull()->defaultValue(0)->after('user_id'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('gamification_activities', 'object_type', $this->string()->notNull()->after('user_id'));
        $this->dropColumn('gamification_participants', 'total_user_hit');
    }
}
