<?php

use app\components\CustomMigration;

/**
 * Class m190816_102004_create_table_users_messages */
class m190816_102004_create_table_users_messages extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('users_messages', [
            'id'            => $this->primaryKey(),
            'type'          => $this->string()->notNull(),
            'message_id'     => $this->integer()->notNull(),
            'sender_id'     => $this->integer()->notNull(),
            'recipient_id'  => $this->integer()->notNull(),
            'title'         => $this->string()->notNull(),
            'excerpt'       => $this->string()->null(),
            'content'       => $this->text()->null(),
            'status'        => $this->integer()->notNull(),
            'meta'          => $this->json()->null(),
            'read_at'       => $this->integer()->null(),
            'created_at'    => $this->integer()->null(),
            'updated_at'    => $this->integer()->null(),
        ]);

        // Tidak bisa membuat foreign key untuk message_id
        // karena akan ada lebih dari satu tabel yang menjadi acuan message_id
        // dan setiap tabel mempunyai auto-increment id sendiri
        // (misalnya broadcast, polling, survey)

        $this->addForeignKey(
            'fk-users_messages-sender_id',
            'users_messages',
            'sender_id',
            'user',
            'id',
            'CASCADE'
        );

        $this->addForeignKey(
            'fk-users_messages-recipient_id',
            'users_messages',
            'recipient_id',
            'user',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey(
            'fk-users_messages-sender_id',
            'users_messages'
        );

        $this->dropForeignKey(
            'fk-users_messages-recipient_id',
            'users_messages'
        );

        $this->dropTable('users_messages');
    }
}
