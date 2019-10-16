<?php

namespace app\models;

use app\components\ModelHelper;
use app\validator\InputCleanValidator;
use Jdsteam\Sapawarga\Behaviors\AreaBehavior;
use Jdsteam\Sapawarga\Jobs\MessageJob;
use Jdsteam\Sapawarga\Models\Concerns\HasArea;
use Jdsteam\Sapawarga\Models\Concerns\HasCategory;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

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
 * @property mixed $meta
 * @property int $status
 */
class Broadcast extends \yii\db\ActiveRecord
{
    use HasArea, HasCategory;

    const STATUS_DELETED = -1;
    const STATUS_DRAFT = 0;
    const STATUS_CANCELED = 1;
    const STATUS_SCHEDULED = 5;
    const STATUS_PUBLISHED = 10;

    const CATEGORY_TYPE = 'broadcast';

    // Default topic untuk semua user
    const TOPIC_DEFAULT = 'kabkota';

    /** @var  array push notification metadata */
    public $data;

    /**
     * @var bool
     */
    protected $enableSendPushNotif = false;

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
            [['title', 'description', 'rw', 'meta'], 'trim'],
            ['title', 'string', 'max' => 100],
            ['title', InputCleanValidator::class],
            ['description', 'string', 'max' => 1000],
            ['description', InputCleanValidator::class],
            ['rw', 'string', 'length' => 3],
            [
                'rw',
                'match',
                'pattern' => '/^[0-9]{3}$/',
                'message' => Yii::t('app', 'error.rw.pattern')
            ],
            ['rw', 'default'],
            [['author_id', 'kabkota_id', 'kec_id', 'kel_id', 'status'], 'integer'],
            ['meta', 'default'],
        ];

        return array_merge($rules, $this->rulesCategory());
    }

    public function fields()
    {
        $fields = [
            'id',
            'author_id',
            'author' => function () {
                return [
                    'id'            => $this->author->id,
                    'name'          => $this->author->name,
                    'role_label'    => $this->author->getRoleLabel(),
                ];
            },
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
            'meta',
            'status',
            'status_label' => function () {
                $statusLabel = '';
                switch ($this->status) {
                    case self::STATUS_PUBLISHED:
                        $statusLabel = Yii::t('app', 'status.published');
                        break;
                    case self::STATUS_DRAFT:
                        $statusLabel = Yii::t('app', 'status.draft');
                        break;
                    case self::STATUS_DELETED:
                        $statusLabel = Yii::t('app', 'status.deleted');
                        break;
                }
                return $statusLabel;
            },
            'data' => function () {
                return $this->data;
            },
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
            BlameableBehavior::class,
            AreaBehavior::class,
        ];
    }

    public function afterSave($insert, $changedAttributes)
    {
        $isSendNotification = ModelHelper::isSendNotification($insert, $changedAttributes, $this);
        if ($isSendNotification) {
            // Send job queue to insert user_messages per user
            Yii::$app->queue->push(new MessageJob([
                'type' => self::CATEGORY_TYPE,
                'sender_id' => $this->author_id,
                'instance' => $this,
                'enable_push_notif' => $this->enableSendPushNotif,
                'push_notif_payload' => $this->getPushNotifPayload(),
            ]));
        }

        return parent::afterSave($insert, $changedAttributes);
    }

    public function getPushNotifPayload()
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

        // By default, send notification to all users
        $topic = Broadcast::TOPIC_DEFAULT;
        if ($this->kel_id && $this->rw) {
            $topic = "{$this->kel_id}_{$this->rw}";
        } elseif ($this->kel_id) {
            $topic = (string) $this->kel_id;
        } elseif ($this->kec_id) {
            $topic = (string) $this->kec_id;
        } elseif ($this->kabkota_id) {
            $topic = (string) $this->kabkota_id;
        }

        return [
            'title'         => $this->title,
            'description'   => $this->description,
            'data'          => $data,
            'topic'         => $topic,
        ];
    }

    public function setEnableSendPushNotif($boolean)
    {
        $this->enableSendPushNotif = $boolean;
    }

    /**
     * Checks if category type is broadcast
     *
     * @param $attribute
     * @param $params
     */
    public function validateCategoryID($attribute, $params)
    {
        ModelHelper::validateCategoryID($this, $attribute);
    }
}
