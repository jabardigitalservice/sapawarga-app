<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "gamification_activities".
 *
 * @property int id
 * @property int gamification_id
 * @property int object_id
 * @property int user_id
 * @property int created_at
 * @property int updated_at
 */

class GamificationActivity extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'gamification_activities';
    }

    public function getGamification()
    {
        return $this->hasOne(Gamification::className(), ['id' => 'gamification_id']);
    }

    public function getTotalHit()
    {
        return $this->gamification->total_hit;
    }

    public function getObjectEvent()
    {
        return $this->gamification->object_event;
    }

    public function fields()
    {
        $fields = [
            'id',
            'gamification_id',
            'object_id',
            'user_id',
            'created_at',
            'updated_at',
            'total_hit' => 'TotalHit',
            'object_event' => 'ObjectEvent',
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
