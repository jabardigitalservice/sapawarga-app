<?php

namespace Jdsteam\Sapawarga\Models\Concerns;

use app\models\Like;
use Yii;

trait HasUserLiked
{
    public function getIsUserLiked()
    {
        $isLiked = Like::find()
            ->where(['entity_id' => $this->id])
            ->andWhere(['type' => Like::TYPE_USER_POST])
            ->andWhere(['user_id' => Yii::$app->user->id])
            ->exists();

        return $isLiked;
    }
}
