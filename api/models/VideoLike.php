<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "video_likes".
 *
 * @property int $video_id
 * @property int $user_id
 */
class VideoLike extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'video_likes';
    }
}
