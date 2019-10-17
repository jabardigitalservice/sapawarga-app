<?php

namespace app\models;

use app\validator\InputCleanValidator;
use Jdsteam\Sapawarga\Behaviors\AreaBehavior;
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
 * @property mixed $meta
 * @property bool $is_scheduled
 * @property mixed $scheduled_datetime
 * @property int $status
 */
class Broadcast extends ActiveRecord
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
            ['is_scheduled', 'required'],
            ['is_scheduled', 'boolean', 'trueValue' => true, 'falseValue' => false, 'strict' => true],
            ['scheduled_datetime', 'default'],
            ['scheduled_datetime', 'required', 'when' => function ($model) {
                return $model->is_scheduled === true;
            }],
            ['status', 'in', 'range' => [-1, 0, 1, 5, 10]],
        ];

        return array_merge($rules, $this->rulesCategory());
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

    public function createPushNotifPayload()
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

    /** @inheritdoc */
    public function beforeSave($insert)
    {
        if ($this->is_scheduled === false) {
            $this->scheduled_datetime = null;
        }

        return parent::beforeSave($insert);
    }

    public function isScheduled(): bool
    {
        return $this->is_scheduled;
    }
}
