<?php

namespace app\models;

use app\components\ModelHelper;
use app\validator\InputCleanValidator;
use Jdsteam\Sapawarga\Models\Concerns\HasActiveStatus;
use Jdsteam\Sapawarga\Models\Contracts\ActiveStatus;
use Yii;
use yii\behaviors\AttributeTypecastBehavior;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "news".
 *
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string $cover_path
 * @property string $cover_path_url
 * @property string $source_url
 * @property string $source_date
 * @property string $content
 * @property string $channel_id
 * @property \app\models\NewsChannel $channel
 * @property int $kabkota_id
 * @property bool $is_push_notification
 * @property array $meta
 * @property int $status
 */
class News extends ActiveRecord implements ActiveStatus
{
    use HasActiveStatus;

    public const STATUS_PUBLISHED = 10;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'news';
    }

    public function getChannel()
    {
        return $this->hasOne(NewsChannel::class, ['id' => 'channel_id']);
    }

    public function getKabkota()
    {
        return $this->hasOne(Area::className(), ['id' => 'kabkota_id']);
    }

    public function getLikes()
    {
        return $this->hasMany(Like::class, ['entity_id' => 'id'])
                    ->andOnCondition(['type' => Like::TYPE_NEWS]);
    }

    public function getIsUserLiked()
    {
        return ModelHelper::getIsUserLiked($this->id, Like::TYPE_NEWS);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['title', 'string', 'max' => 100],
            ['title', 'string', 'min' => 10],
            ['title', InputCleanValidator::class],

            [['title', 'cover_path', 'source_url', 'source_date', 'content'], 'trim'],
            [['title', 'content', 'cover_path', 'source_url'], 'safe'],

            [
                ['title', 'channel_id', 'cover_path', 'source_url', 'source_date', 'content', 'status'],
                'required',
            ],

            ['content', 'string', 'max' => 65000],

            ['source_date', 'date', 'format' => 'php:Y-m-d'],
            ['source_url', 'url'],

            ['meta', 'default'],

            ['kabkota_id', 'integer'],
            ['channel_id', 'integer'],

            ['is_push_notification', 'boolean'],

            ['status', 'integer'],
            ['status', 'in', 'range' => [-1, 0, 10]],
        ];
    }

    public function fields()
    {
        $fields = [
            'id',
            'title',
            'content',
            'cover_path',
            'cover_path_url' => 'CoverPathURL',
            'source_date',
            'source_url',
            'channel_id',
            'channel'        => 'ChannelField',
            'kabkota_id',
            'kabkota'      => 'KabkotaField',
            'total_viewers',
            'likes_count',
            'is_liked' => 'IsUserLiked',
            'is_push_notification',
            'meta',
            'status',
            'status_label' => 'StatusLabel',
            'created_at',
            'updated_at',
            'created_by',
        ];

        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'          => 'ID',
            'title'       => 'Sumber',
            'channel_id'  => 'Website',
            'cover_path'  => 'Cover Path',
            'source_date' => 'Tanggal Berita',
            'source_url'  => 'URL Berita',
            'content'     => 'Konten Berita',
            'meta'        => 'Meta',
            'status'      => 'Status',
        ];
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
            ],
            [
                'class'     => SluggableBehavior::class,
                'attribute' => 'title',
            ],
            'typecast' => [
                'class' => AttributeTypecastBehavior::class,
                'attributeTypes' => [
                    'is_push_notification' => AttributeTypecastBehavior::TYPE_BOOLEAN,
                ],
                'typecastAfterValidate' => false,
                'typecastBeforeSave' => false,
                'typecastAfterFind' => true,
            ],
            BlameableBehavior::class,
        ];
    }

    /** @inheritdoc */
    public function beforeSave($insert)
    {
        if ($insert) { // Model is created
            $this->total_viewers = 0;
        }

        return parent::beforeSave($insert);
    }

    /** @inheritdoc */
    public function afterSave($insert, $changedAttributes)
    {
        $isSendNotification = ModelHelper::isSendNotification($insert, $changedAttributes, $this);

        if ($isSendNotification) {
            $categoryName = Notification::CATEGORY_LABEL_NEWS;
            $payload = [
                'categoryName'  => $categoryName,
                'title'         => "{$categoryName}: {$this->title}",
                'description'   => null,
                'target'        => [
                    'kabkota_id'    => $this->kabkota_id,
                ],
                'meta'          => [
                    'target'    => 'news',
                    'id'        => $this->id,
                ],
            ];

            ModelHelper::sendNewContentNotification($payload);
        }

        return parent::afterSave($insert, $changedAttributes);
    }

    protected function getCoverPathURL()
    {
        $publicBaseUrl = Yii::$app->params['storagePublicBaseUrl'];
        return "{$publicBaseUrl}/{$this->cover_path}";
    }

    protected function getChannelField()
    {
        return [
            'id'       => $this->channel->id,
            'name'     => $this->channel->name,
            'website'  => $this->channel->website,
            'icon_url' => $this->channel->icon_url,
        ];
    }

    protected function getKabkotaField()
    {
        if ($this->kabkota) {
            return [
                'id'   => $this->kabkota->id,
                'name' => $this->kabkota->name,
            ];
        } else {
            return null;
        }
    }
}
