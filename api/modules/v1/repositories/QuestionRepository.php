<?php

namespace app\modules\v1\repositories;

use app\models\Question;

class QuestionRepository
{
    public function getDetail($id)
    {
        $query = Question::find()->with('comments')
                ->select([
                    '{{questions}}.*',
                    'COUNT({{likes}}.id) AS likes_count'
                ])
                ->joinWith('likes')
                ->where(['<>', 'status', Question::STATUS_DELETED])
                ->where(['questions.id' => $id])
                ->groupBy('{{questions}}.id')
                ->one();

        return $query;
    }
}
