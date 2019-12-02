<?php

use app\components\CustomMigration;

/**
 * Class m191202_081306_create_table_questions_and_questions_comments */
class m191202_081306_create_table_questions_and_questions_comments extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('questions', [
            "id" => $this->primaryKey(),
            "text" => $this->string()->notNull(),
            // Model file needs 'user_name', 'user_photo_url', and 'user_role_id' properties,
            // taking reference from 'created_by'
            "top_comment_id" => $this->integer(),
            "is_liked" => $this->boolean()->defaultValue(false),
            // Properties 'likes_count' and 'comments_count' will be defined in model file
            "status" => $this->integer()->notNull(),
            "created_by" => $this->integer()->notNull(),
            "updated_by" => $this->integer()->notNull(),
            "created_at" => $this->integer()->notNull(),
            "updated_at" => $this->integer()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-questions-top_comment_id',
            'questions',
            'top_comment_id',
            'comments',
            'id',
            'SET NULL'
        );

        $this->addForeignKey(
            'fk-questions-created_by',
            'questions',
            'created_by',
            'user',
            'id',
            'SET NULL'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m191202_081306_create_table_questions_and_questions_comments cannot be reverted.\n";

        return false;
    }
}
