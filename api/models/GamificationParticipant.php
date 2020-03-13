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

    public function getAuthor()
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function fields()
    {
        $fields = [
            'id',
            'gamification_id',
            'user_id',
            'user' => 'AuthorField',
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

    protected function getAuthorField()
    {
        return [
            'id' => $this->author->id,
            'name' => $this->author->name,
            'email' => $this->author->email,
            'kabkota' => isset($this->author->kabkota->name) ? $this->author->kabkota->name : null,
            'kelurahan' => isset($this->author->kelurahan->name) ? $this->author->kelurahan->name : null,
            'kecamatan' => isset($this->author->kecamatan->name) ? $this->author->kecamatan->name : null,
            'rw' => isset($this->author->rw) ? $this->author->rw : null,
            'role_label' => $this->author->getRoleName(),
        ];
    }
}
