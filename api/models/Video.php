<?php

namespace app\models;

use app\validator\InputCleanValidator;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use app\components\ModelHelper;

/**
 * This is the model class for table "video".
 *
 * @property int $id
 * @property string $title
 * @property int $category_id
 * @property string $source
 * @property string $video_url
 * @property int $kabkota_id
 * @property int $total_likes
 * @property int $seq
 * @property int $status
 * @property int $created_by
 * @property int $updated_by
 * @property int $created_at
 * @property int $updated_at
 */

class Video extends ActiveRecord
{
    const STATUS_DELETED = -1;
    const STATUS_DISABLED = 0;
    const STATUS_ACTIVE = 10;

    const CATEGORY_TYPE = 'video';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'videos';
    }

    public function getCategory()
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    public function getKabkota()
    {
        return $this->hasOne(Area::className(), ['id' => 'kabkota_id']);
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
            ['title', 'trim'],
            [['source','title', 'video_url'], 'safe'],
            [
                ['title', 'category_id', 'source', 'video_url', 'status'],
                'required'
            ],
            [
                ['category_id', 'kabkota_id', 'status','seq'],
                 'integer'
            ],
            ['video_url', 'match', 'pattern' => '/^(https:\/\/www.youtube.com)\/.+$/'],
            ['category_id', 'validateCategoryID'],
            ['source', 'in', 'range' => ['youtube']],
            ['status', 'in', 'range' => [-1, 0, 10]],
            ['seq', 'in', 'range' => [1, 2, 3, 4, 5]],
        ];
    }

    public function fields()
    {
        $fields = [
            'id',
            'title',
            'category_id',
            'category' => function () {
                return [
                    'id'   => $this->category->id,
                    'name' => $this->category->name,
                ];
            },
            'source',
            'video_url',
            'kabkota_id',
            'kabkota' => function () {
                if ($this->kabkota) {
                    return [
                        'id'   => $this->kabkota->id,
                        'name' => $this->kabkota->name,
                    ];
                } else {
                    return null;
                }
            },
            'total_likes',
            'seq',
            'status',
            'status_label' => function () {
                return $this->getStatusLabel();
            },
            'created_at',
            'updated_at',
        ];

        return $fields;
    }

    protected function getStatusLabel()
    {
        $statusLabel = '';

        switch ($this->status) {
            case self::STATUS_ACTIVE:
                $statusLabel = Yii::t('app', 'status.active');
                break;
            case self::STATUS_DISABLED:
                $statusLabel = Yii::t('app', 'status.inactive');
                break;
            case self::STATUS_DELETED:
                $statusLabel = Yii::t('app', 'status.deleted');
                break;
        }

        return $statusLabel;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Judul',
            'category_id' => 'Kategori',
            'source' => 'Sumber Video',
            'url' => 'URL',
            'kabkota_id' => 'KAB / KOTA',
            'seq' => 'Prioritas',
            'status' => 'Status',
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
            BlameableBehavior::class,
        ];
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
