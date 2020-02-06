<?php

namespace app\modules\v1\repositories;

use app\models\UserPost;

class UserPostRepository
{
    public function getDetail($id)
    {
        $query = UserPost::find()->with('comments')
                ->where(['<>', 'status', UserPost::STATUS_DELETED])
                ->where(['user_posts.id' => $id])
                ->one();

        return $query;
    }
}
