<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "likes".
 *
 * @property int $id
 * @property int $type
 * @property int $user_id
 * @property int $entity_id
 * @property int $created_at
 * @property int $updated_at
 */
class Like extends ActiveRecord
{
    public const TYPE_VIDEO = 'video';
    public const TYPE_QUESTION = 'question';
    public const TYPE_USER_POST = 'user_post';
    public const TYPE_NEWS = 'news';
    public const TYPE_NEWS_IMPORTANT = 'news_important';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'likes';
    }

    /** @inheritdoc */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => time(),
            ]
        ];
    }
}
