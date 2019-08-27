<?php

namespace app\models;

use app\validator\InputCleanValidator;
use Jdsteam\Sapawarga\Models\Concerns\HasActiveStatus;
use Jdsteam\Sapawarga\Models\Concerns\HasArea;
use Jdsteam\Sapawarga\Models\Concerns\HasCategory;
use Jdsteam\Sapawarga\Models\Contracts\ActiveStatus;
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

class Video extends ActiveRecord implements ActiveStatus
{
    use HasArea, HasCategory, HasActiveStatus;

    const CATEGORY_TYPE = 'video';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'videos';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = [
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
            ['source', 'in', 'range' => ['youtube']],
            ['status', 'in', 'range' => [-1, 0, 10]],
            ['seq', 'in', 'range' => [1, 2, 3, 4, 5]],
        ];

        return array_merge($rules, $this->rulesCategory());
    }

    public function fields()
    {
        $fields = [
            'id',
            'title',
            'category_id',
            'category' => 'CategoryField',
            'source',
            'video_url',
            'kabkota_id',
            'kabupaten' => 'KabkotaField',
            'total_likes',
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
}
