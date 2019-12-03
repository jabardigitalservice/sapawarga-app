<?php

use yii\db\Migration;

/**
 * Handles adding is_flagged column to table `{{%questions}}` and `{{%question_comments}}`.
 */
class m191203_075636_add_is_flagged_column_to_questions_and_question_comments_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn(
            'questions',
            'is_flagged',
            $this->boolean()->defaultValue(false)->after('answer_id')
        );

        $this->addColumn(
            'question_comments',
            'is_flagged',
            $this->boolean()->defaultValue(false)->after('text')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('question_comments', 'is_flagged');
        $this->dropColumn('questions', 'is_flagged');
    }
}
