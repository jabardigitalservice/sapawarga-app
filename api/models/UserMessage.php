<?php

namespace app\models;

use Jdsteam\Sapawarga\Models\Concerns\HasHashedId;
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
    use HasHashedId;

    public const STATUS_DELETED = -1;
    public const STATUS_ACTIVE = 10;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_messages';
    }

    public function getCategory()
    {
        if ($this->message) {
            return $this->message->category;
        } else {
            return null;
        }
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

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [
                ['type', 'message_id', 'sender_id', 'recipient_id', 'title', 'content', 'status'], 'required',
            ],
        ];
    }

    public function fields()
    {
        $fields = [
            'id' => 'HashedId',
            'type',
            'message_id',
            'sender_id',
            'sender_name' => function () {
                return $this->sender ? $this->sender->name : null;
            },
            'recipient_id',
            'category_name' => function () {
                return $this->category ? $this->category->name : null;
            },
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
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => time(),
            ],
        ];
    }
}
