<?php

use app\components\CustomMigration;

/**
 * Class m200904_065552_update_user_messages_message_id_indexing */
class m200904_065552_update_user_messages_message_id_indexing extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createIndex('idx-message-id', 'user_messages', ['message_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex('idx-message-id', 'user_messages');
    }
}
