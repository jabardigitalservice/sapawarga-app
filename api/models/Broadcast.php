<?php

namespace app\models;

use app\validator\InputCleanValidator;
use Jdsteam\Sapawarga\Behaviors\AreaBehavior;
use Jdsteam\Sapawarga\Jobs\MessageJob;
use Jdsteam\Sapawarga\Models\Concerns\HasArea;
use Jdsteam\Sapawarga\Models\Concerns\HasCategory;
use Yii;
use yii\behaviors\AttributeTypecastBehavior;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "broadcasts".
 *
 * @property int $id
 * @property int $author_id
 * @property int $category_id
 * @property string $title
 * @property string $description
 * @property int $kabkota_id
 * @property int $kec_id
 * @property int $kel_id
 * @property string $rw
 * @property string $type
 * @property string $link_url
 * @property string $internal_object_type
 * @property int $internal_object_id
 * @property string $internal_object_name
 * @property mixed $meta
 * @property bool $is_scheduled
 * @property mixed $scheduled_datetime
 * @property int $status
 */
class Broadcast extends ActiveRecord
{
    use HasArea;
    use HasCategory;

    public const STATUS_DELETED = -1;
    public const STATUS_DRAFT = 0;
    public const STATUS_CANCELED = 1;
    public const STATUS_SCHEDULED = 5;
    public const STATUS_PUBLISHED = 10;

    // Category type for message user, see MessageJob on insertUserMessages()
    public const CATEGORY_TYPE = 'broadcast';

    // Default topic untuk semua user
    public const TOPIC_DEFAULT = 'kabkota';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'broadcasts';
    }

    public function getAuthor()
    {
        return $this->hasOne(User::class, ['id' => 'author_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = [
            [['title', 'status'], 'required'],
            [['title', 'description', 'rw', 'meta', 'link_url', 'internal_object_name'], 'trim'],
            ['title', 'string', 'max' => 100],
            ['title', InputCleanValidator::class],
            ['rw', 'string', 'length' => 3],
            [['author_id', 'kabkota_id', 'kec_id', 'kel_id', 'internal_object_id', 'status'], 'integer'],
            ['meta', 'default'],

            ['type', 'in', 'range' => ['internal', 'external']],
            ['type', 'validateTypeInternal'],
            ['type', 'validateTypeExternal'],
            ['link_url', 'url'],
            ['internal_object_type', 'in', 'range' => ['news', 'news-important', 'polling', 'survey']],

            ['is_scheduled', 'default', 'value' => false],
            ['is_scheduled', 'required'],
            ['is_scheduled', 'boolean', 'trueValue' => true, 'falseValue' => false, 'strict' => true],
            ['scheduled_datetime', 'default'],
            ['scheduled_datetime', 'required', 'when' => function ($model) {
                return $model->is_scheduled === true;
            }
            ],
            ['scheduled_datetime', 'datetime', 'timestampAttribute' => 'scheduled_datetime'],
            ['scheduled_datetime', 'validateScheduledDateTime'],

            ['status', 'in', 'range' => [-1, 0, 1, 5, 10]],
        ];

        return array_merge($rules, $this->rulesRw(), $this->rulesCategory());
    }

    public function fields()
    {
        $fields = [
            'id',
            'author_id',
            'author' => 'AuthorField',
            'category_id',
            'category' => 'CategoryField',
            'title',
            'description',
            'kabkota_id',
            'kabkota' => 'KabkotaField',
            'kec_id',
            'kecamatan' => 'KecamatanField',
            'kel_id',
            'kelurahan' => 'KelurahanField',
            'rw',
            'type',
            'link_url',
            'internal_object_type',
            'internal_object_id',
            'internal_object_name',
            'meta',
            'is_scheduled',
            'scheduled_datetime',
            'status',
            'status_label' => 'StatusLabel',
            'created_at',
            'updated_at',
        ];

        return $fields;
    }

    public function getAuthorField()
    {
        return [
            'id'            => $this->author->id,
            'name'          => $this->author->name,
            'role_label'    => $this->author->getRoleLabel(),
        ];
    }

    public function getStatusLabel()
    {
        $statuses = [
            self::STATUS_PUBLISHED => Yii::t('app', 'status.published'),
            self::STATUS_SCHEDULED => Yii::t('app', 'status.scheduled'),
            self::STATUS_CANCELED  => Yii::t('app', 'status.canceled'),
            self::STATUS_DRAFT     => Yii::t('app', 'status.draft'),
            self::STATUS_DELETED   => Yii::t('app', 'status.deleted'),
        ];

        return $statuses[$this->status];
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
            'typecast' => [
                'class' => AttributeTypecastBehavior::class,
                'attributeTypes' => [
                    'is_scheduled' => AttributeTypecastBehavior::TYPE_BOOLEAN,
                ],
                'typecastAfterValidate' => false,
                'typecastBeforeSave' => false,
                'typecastAfterFind' => true,
            ],
            BlameableBehavior::class,
            AreaBehavior::class,
        ];
    }

    /**
     * Build array of information for FCM
     *
     * @return array
     */
    public function buildPushNotificationPayload(): array
    {
        $data = [
            'target'            => 'broadcast',
            'id'                => $this->id,
            'author'            => $this->author->name,
            'title'             => $this->title,
            'category_name'     => $this->category->name,
            'description'       => $this->description,
            'updated_at'        => $this->updated_at ?? time(),
            'push_notification' => true,
        ];

        return [
            'title'         => $this->title,
            'description'   => null,
            'data'          => $data,
            'topic'         => $this->buildTopicName(),
        ];
    }

    /**
     * Build topic name for Push Notification targets
     *
     * @return string
     */
    protected function buildTopicName(): string
    {
        // By default, send notification to all users
        $topic = self::TOPIC_DEFAULT;

        if ($this->kel_id && $this->rw) {
            $topic = "{$this->kel_id}_{$this->rw}";
        } elseif ($this->kel_id) {
            $topic = (string) $this->kel_id;
        } elseif ($this->kec_id) {
            $topic = (string) $this->kec_id;
        } elseif ($this->kabkota_id) {
            $topic = (string) $this->kabkota_id;
        }

        return $topic;
    }

    /** @inheritdoc */
    public function beforeSave($insert)
    {
        if ($this->is_scheduled === false) {
            $this->scheduled_datetime = null;
        }

        return parent::beforeSave($insert);
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isScheduled(): bool
    {
        return $this->is_scheduled;
    }

    /**
     * Check if scheduled datetime passed
     *
     * @return bool
     */
    public function isDue(): bool
    {
        $now = time();

        return $now >= $this->scheduled_datetime;
    }

    /**
     * Custom validation rules for input Scheduled Datetime
     * Scheduled Datetime must greater than now
     *
     * @throws \Exception
     */
    public function validateScheduledDateTime()
    {
        $now = time();

        if ($this->scheduled_datetime <= $now) {
            $this->addError(
                'scheduled_datetime',
                Yii::t('app', 'error.scheduled_datetime.must_after_now')
            );
        }
    }

    public function validateTypeInternal($attribute, $params)
    {
        if ($this->type === 'internal') {
            if (empty($this->internal_object_type)) {
                $this->addError($attribute, Yii::t('app', 'error.empty.internalfill'));
            }
        }
    }

    public function validateTypeExternal($attribute, $params)
    {
        if ($this->type === 'external') {
            if (empty($this->link_url)) {
                $this->addError($attribute, Yii::t('app', 'error.empty.externalfill'));
            }
        }
    }

    /**
     * Insert new queue for broadcast message to users (async)
     *
     * @param \app\models\Broadcast $broadcast
     * @return void
     */
    public static function pushSendMessageToUserJob(Broadcast $broadcast): void
    {
        Yii::$app->queue->push(new MessageJob([
            'type'              => $broadcast::CATEGORY_TYPE,
            'senderId'          => $broadcast->author_id,
            'title'             => $broadcast->title,
            'content'           => $broadcast->description,
            'instance'          => $broadcast->toArray(),
            'pushNotifyPayload' => $broadcast->buildPushNotificationPayload(),
        ]));
    }
}
