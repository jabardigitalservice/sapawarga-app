<?php

namespace app\modules\v1\repositories;

use app\models\UserPost;

class UserPostRepository
{
    public function getDetail($id)
    {
        $query = UserPost::find()->with('comments')
                ->select([
                    '{{user_posts}}.*',
                    'COUNT({{likes}}.id) AS likes_count'
                ])
                ->joinWith('likes')
                ->where(['<>', 'status', UserPost::STATUS_DELETED])
                ->where(['user_posts.id' => $id])
                ->groupBy('{{user_posts}}.id')
                ->one();

        return $query;
    }
}
