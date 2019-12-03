<?php

namespace app\models;

use yii\db\ActiveRecord;

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
    const TYPE_VIDEO = 'video';
    const TYPE_QUESTION = 'question';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'likes';
    }
}
