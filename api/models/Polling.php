<?php

namespace app\models;

use app\components\ModelHelper;
use app\validator\InputCleanValidator;
use Jdsteam\Sapawarga\Behaviors\AreaBehavior;
use Yii;
use yii\behaviors\AttributeTypecastBehavior;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "polling".
 *
 * @property int $id
 * @property int $category_id
 * @property string $name
 * @property string $description
 * @property string $excerpt
 * @property string $question
 * @property string $start_date
 * @property string $end_date
 * @property int $kabkota_id
 * @property int $kec_id
 * @property int $kel_id
 * @property string $rw
 * @property bool $is_push_notification
 * @property mixed $meta
 * @property int $status
 */
class Polling extends ActiveRecord
{
    const STATUS_DELETED = -1;
    const STATUS_DRAFT = 0;
    const STATUS_DISABLED = 1;
    const STATUS_PUBLISHED = 10;
    const STATUS_STARTED = 15;
    const STATUS_ENDED = 20;

    const CATEGORY_TYPE = 'polling';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'polling';
    }

    public function getCategory()
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    public function getKelurahan()
    {
        return $this->hasOne(Area::className(), ['id' => 'kel_id']);
    }

    public function getKecamatan()
    {
        return $this->hasOne(Area::className(), ['id' => 'kec_id']);
    }

    public function getKabkota()
    {
        return $this->hasOne(Area::className(), ['id' => 'kabkota_id']);
    }

    public function getAnswers()
    {
        return $this->hasMany(PollingAnswer::class, ['polling_id' => 'id']);
    }

    public function getVotes()
    {
        return $this->hasMany(PollingVote::class, ['polling_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [
                ['name', 'description', 'excerpt', 'question', 'status', 'start_date', 'end_date', 'category_id'],
                'required',
            ],
            [['name', 'description', 'excerpt', 'question', 'rw', 'meta'], 'trim'],
            [['name', 'question'], 'string', 'min' => 10],
            [['name', 'question'], 'string', 'max' => 100],
            [['name', 'description', 'excerpt', 'question'], InputCleanValidator::class],

            [['description', 'excerpt'], 'string', 'max' => 1024 * 12],

            ['rw', 'string', 'length' => 3],
            [
                'rw',
                'match',
                'pattern' => '/^[0-9]{3}$/',
                'message' => Yii::t('app', 'error.rw.pattern'),
            ],
            [['rw', 'meta', 'created_by', 'updated_by'], 'default'],
            [['category_id', 'kabkota_id', 'kec_id', 'kel_id', 'status'], 'integer'],
            ['category_id', 'validateCategoryID'],

            [['start_date', 'end_date'], 'date', 'format' => 'php:Y-m-d'],
            [
                'start_date',
                'compare',
                'compareAttribute'       => 'end_date',
                'operator'               => '<',
            ],

            ['is_push_notification', 'boolean'],

            ['status', 'in', 'range' => [-1, 0, 1, 10]],
        ];
    }

    public function fields()
    {
        $fields = [
            'id',
            'category_id',
            'category'     => function () {
                return [
                    'id'   => $this->category->id,
                    'name' => $this->category->name,
                ];
            },
            'name',
            'question',
            'description',
            'excerpt',
            'kabkota_id',
            'kabkota'      => function () {
                if ($this->kabkota) {
                    return [
                        'id'   => $this->kabkota->id,
                        'name' => $this->kabkota->name,
                    ];
                } else {
                    return null;
                }
            },
            'kec_id',
            'kecamatan'    => function () {
                if ($this->kecamatan) {
                    return [
                        'id'   => $this->kecamatan->id,
                        'name' => $this->kecamatan->name,
                    ];
                } else {
                    return null;
                }
            },
            'kel_id',
            'kelurahan'    => function () {
                if ($this->kelurahan) {
                    return [
                        'id'   => $this->kelurahan->id,
                        'name' => $this->kelurahan->name,
                    ];
                } else {
                    return null;
                }
            },
            'rw',
            'answers',
            'start_date',
            'end_date',
            'votes_count' => function () {
                return (int) $this->getVotes()->count();
            },
            'is_push_notification',
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
            'created_at',
            'updated_at',
        ];

        return $fields;
    }

    public function afterSave($insert, $changedAttributes)
    {
        $isSendNotification = ModelHelper::isSendNotification($insert, $changedAttributes, $this);

        if ($isSendNotification) {
            $categoryName = Notification::CATEGORY_LABEL_POLLING;
            $payload = [
                'categoryName'  => $categoryName,
                'title'         => "{$categoryName}: {$this->name}",
                'description'   => $this->description,
                'target'        => [
                    'kabkota_id'    => $this->kabkota_id,
                    'kec_id'        => $this->kec_id,
                    'kel_id'        => $this->kel_id,
                    'rw'            => $this->rw,
                ],
                'meta'          => [
                    'target'    => 'polling',
                    'id'        => $this->id,
                ],
            ];

            ModelHelper::sendNewContentNotification($payload);
        }

        return parent::afterSave($insert, $changedAttributes);
    }

    /** @inheritdoc */
    public function behaviors()
    {
        $behaviors = [
            [
                'class'              => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value'              => time(),
            ],
            'typecast' => [
                'class' => AttributeTypecastBehavior::class,
                'attributeTypes' => [
                    'is_push_notification' => AttributeTypecastBehavior::TYPE_BOOLEAN,
                ],
                'typecastAfterFind' => true,
            ],
            AreaBehavior::class,
        ];

        if (!YII_ENV_TEST) {
            $behaviors[] = BlameableBehavior::class;
        }

        return $behaviors;
    }


    /**
     * Checks if category type is notification
     *
     * @param $attribute
     * @param $params
     */
    public function validateCategoryID($attribute, $params)
    {
        ModelHelper::validateCategoryID($this, $attribute);
    }
}
