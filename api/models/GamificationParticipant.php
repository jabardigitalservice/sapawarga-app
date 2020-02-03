<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "gamification_participants".
 *
 * @property int $id
 * @property int $gamification_id
 * @property int $user_id
 * @property int created_at
 * @property int updated_at
 */

class GamificationParticipant extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'gamification_participants';
    }

    public function getGamification()
    {
        return $this->hasOne(Gamification::className(), ['id' => 'gamification_id']);
    }

    public function getCompleted()
    {
        return ($this->gamification->total_hit == $this->total_user_hit) ? true : false;
    }

    public function fields()
    {
        $fields = [
            'id',
            'gamification_id',
            'user_id',
            'total_user_hit',
            'created_at',
            'updated_at',
            'completed',
            'gamification',
        ];

        return $fields;
    }

    /** @inheritdoc */
    public function behaviors()
    {
        return [
            [
                'class'              => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value'              => time(),
            ]
        ];
    }
}
