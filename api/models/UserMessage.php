<?php

namespace app\models;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "user_messages".
 *
 * @property int $id
 * @property string $type
 * @property int $message_id
 * @property int $sender_id
 * @property int $recipient_id
 * @property string $title
 * @property string $excerpt
 * @property string $content
 * @property int $status
 * @property mixed $meta
 * @property int $read_at
 */
class UserMessage extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_messages';
    }

    public function getMessage()
    {
        $result = '';
        switch ($this->type) {
            case Broadcast::CATEGORY_TYPE:
                $result = $this->hasOne(Broadcast::class, ['id' => 'message_id']);
                break;
            default:
                break;
        }
        return $result;
    }

    public function getSender()
    {
        return $this->hasOne(User::class, ['id' => 'sender_id']);
    }

    public function getRecipient()
    {
        return $this->hasOne(User::class, ['id' => 'recipient_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [
                ['type', 'message_id', 'sender_id', 'recipient_id', 'title', 'status'], 'required',
            ],
        ];
    }

    public function fields()
    {
        $fields = [
            'id',
            'type',
            'message_id',
            'sender_id',
            'recipient_id',
            'title',
            'excerpt',
            'content',
            'status',
            'meta',
            'read_at',
            'created_at',
            'updated_at',
        ];

        return $fields;
    }

    /** @inheritdoc */
    public function behaviors()
    {
        return [
            [
                'class'              => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value'              => time(),
            ],
        ];
    }
}
