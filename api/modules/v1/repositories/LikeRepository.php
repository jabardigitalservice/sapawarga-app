<?php

namespace app\modules\v1\repositories;

use Yii;
use app\models\Like;

class LikeRepository
{
    public function setLikeUnlike($id, $type)
    {
        $userId = Yii::$app->user->getId();
        $userLike = Like::find()->where(['entity_id' => $id])
            ->andWhere(['type' => $type])
            ->andWhere(['user_id' => $userId])
            ->one();

        if (! empty($userLike)) {
            $unlike = Like::findOne($userLike->id);
            $unlike->delete();
        } else {
            $like = new Like();
            $like->entity_id = $id;
            $like->user_id = $userId;
            $like->type = $type;
            $like->save();
        }

        return true;
    }
}
