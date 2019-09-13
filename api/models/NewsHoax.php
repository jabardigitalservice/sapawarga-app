<?php

namespace app\models;

use app\validator\InputCleanValidator;
use Jdsteam\Sapawarga\Models\Concerns\HasActiveStatus;
use Jdsteam\Sapawarga\Models\Concerns\HasCategory;
use Jdsteam\Sapawarga\Models\Contracts\ActiveStatus;
use Yii;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "news_hoax".
 *
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string $cover_path
 * @property string $cover_path_url
 * @property string $source_url
 * @property string $source_date
 * @property string $content
 * @property string $category_id
 * @property \app\models\Category $category
 * @property array $meta
 * @property int $seq
 * @property int $status
 */
class NewsHoax extends ActiveRecord implements ActiveStatus
{
    use HasActiveStatus, HasCategory;

    const CATEGORY_TYPE = 'newsHoax';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'news_hoax';
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
                ['title', 'category_id', 'cover_path', 'content', 'status'],
                'required',
            ],

            ['content', 'string', 'max' => 65000],

            ['source_date', 'date', 'format' => 'php:Y-m-d'],
            ['source_url', 'url'],

            ['meta', 'default'],


            ['category_id', 'integer'],
            ['status', 'integer'],
            ['seq', 'integer'],

            ['status', 'in', 'range' => [
                ActiveStatus::STATUS_DELETED,
                ActiveStatus::STATUS_DISABLED,
                ActiveStatus::STATUS_ACTIVE
            ]],
        ];
    }

    public function fields()
    {
        $bucket = Yii::$app->fileStorage->getBucket('imageFiles');

        $fields = [
            'id',
            'title',
            'content',
            'cover_path',
            'cover_path_url' => function () use ($bucket) {
                return $bucket->getFileUrl($this->cover_path);
            },
            'source_date',
            'source_url',
            'category_id',
            'category' => 'CategoryField',
            'meta',
            'seq',
            'status',
            'status_label' => 'StatusLabel',
            'created_at',
            'updated_at',
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
            'category_id' => 'Kategori',
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
        ];
    }
}
