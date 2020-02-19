<?php

use app\components\CustomMigration;

/**
 * Class m200218_043435_update_news_questions_like_comment_count */
class m200218_043435_update_news_questions_like_comment_count extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // News
        $this->addColumn('news', 'likes_count', $this->integer()->defaultValue(0)->after('total_viewers'));
        // News Likes
        \Yii::$app->db->createCommand('
            UPDATE news
            INNER JOIN (
                SELECT entity_id, count(id) total_likes
                FROM likes
                where type = :type
                group by entity_id
            ) as likes ON likes.entity_id = news.id
            set news.likes_count = likes.total_likes')
            ->bindValue(':type', 'news')
            ->execute();


        // Questions
        $this->addColumn('questions', 'comments_count', $this->integer()->defaultValue(0)->after('is_flagged'));
        $this->addColumn('questions', 'likes_count', $this->integer()->defaultValue(0)->after('is_flagged'));
        // Question likes
        \Yii::$app->db->createCommand('
                UPDATE questions
                INNER JOIN (
                    SELECT entity_id, count(id) total_likes
                    FROM likes
                    where type = :type
                    group by entity_id
                ) as likes ON likes.entity_id = questions.id
                set questions.likes_count = likes.total_likes')
            ->bindValue(':type', 'question')
            ->execute();
        // Question comment
        \Yii::$app->db->createCommand('
                UPDATE questions q
                INNER JOIN (
                    SELECT question_id, count(id) as total_comment FROM question_comments
                    where status = 10
                    group by question_id
                ) as comments ON comments.question_id = q.id
                set q.comments_count = comments.total_comment')
            ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('news', 'likes_count');

        $this->dropColumn('questions', 'comments_count');
        $this->dropColumn('questions', 'likes_count');
    }
}
