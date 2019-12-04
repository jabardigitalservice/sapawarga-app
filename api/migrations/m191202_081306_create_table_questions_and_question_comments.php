<?php

use app\components\CustomMigration;

/**
 * Class m191202_081306_create_table_questions_and_question_comments */
class m191202_081306_create_table_questions_and_question_comments extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('questions', [
            'id' => $this->primaryKey(),
            'text' => $this->string()->notNull(),
            // Model file needs 'user_name', 'user_photo_url', and 'user_role_id' properties,
            // taking reference from 'created_by'
            'answer_id' => $this->integer(),
            // Properties 'is_liked', 'likes_count', and 'comments_count' will be defined in model file
            'status' => $this->integer()->notNull(),
            // 'created_by' and 'updated_by' are nullable to allow deleted users
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->createTable('question_comments', [
            'id' => $this->primaryKey(),
            'question_id' => $this->integer(),
            'text' => $this->string()->notNull(),
            // Model file needs 'user_name', 'user_photo_url', and 'user_role_id' properties,
            // taking reference from 'created_by'
            'status' => $this->integer()->notNull(),
            // 'created_by' and 'updated_by' are nullable to allow deleted users
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->addForeignKey(
            'fk-questions-answer_id',
            'questions',
            'answer_id',
            'question_comments',
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

        $this->addForeignKey(
            'fk-question_comments-question_id',
            'question_comments',
            'question_id',
            'questions',
            'id',
            'SET NULL'
        );

        $this->addForeignKey(
            'fk-question_comments-created_by',
            'question_comments',
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
        $this->dropForeignKey('fk-question_comments-created_by', 'question_comments');
        $this->dropForeignKey('fk-question_comments-question_id', 'question_comments');

        $this->dropForeignKey('fk-questions-created_by', 'questions');
        $this->dropForeignKey('fk-questions-answer_id', 'questions');

        $this->dropTable('question_comments');
        $this->dropTable('questions');
    }
}
