<?php

namespace app\models;

use app\components\ModelHelper;
use app\validator\InputCleanValidator;
use Jdsteam\Sapawarga\Models\Concerns\HasActiveStatus;
use Jdsteam\Sapawarga\Models\Contracts\ActiveStatus;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "gamification".
 *
 * @property int $id
 * @property string $title
 * @property string $title_badge
 * @property string $image_badge_path
 * @property string $description
 * @property string $object_type
 * @property string $object_event
 * @property int $total_hit
 * @property int $status
 * @property date $start_date
 * @property date $end_date
 * @property int $created_by
 * @property int $updated_by
 * @property int $created_at
 * @property int $updated_at
 *
 */

class Gamification extends ActiveRecord implements ActiveStatus
{
    use HasActiveStatus;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'gamifications';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['title', 'string', 'min' => 10],
            ['title', InputCleanValidator::class],

            [['title','title_badge','image_badge_path','description','object_type','object_event',
                'total_hit','status','start_date','end_date'], 'trim'],

            [['title','title_badge','image_badge_path','description','object_type','object_event',
                'total_hit','status','start_date','end_date'], 'safe'],

            [['title','title_badge','image_badge_path','description','object_type','object_event',
                'total_hit','status','start_date','end_date'], 'required'],

            [['start_date', 'end_date'], 'date', 'format' => 'php:Y-m-d H:i:s'],
            ['start_date', 'compare', 'compareAttribute' => 'end_date', 'operator' => '<'],
            ['end_date', 'compare', 'compareAttribute' => 'start_date', 'operator' => '>'],

            [['status', 'total_hit'], 'integer'],

            ['status', 'in', 'range' => [-1, 0, 10]],
        ];
    }

    public function fields()
    {
        $fields = [
            'id',
            'title',
            'title_badge',
            'description',
            'object_type',
            'object_event',
            'total_hit',
            'image_badge_path_url' => function () {
                $publicBaseUrl = Yii::$app->params['storagePublicBaseUrl'];
                return "{$publicBaseUrl}/{$this->image_badge_path}";
            },
            'start_date',
            'end_date',
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
            'id' => 'ID',
            'title' => 'Judul',
            'title_badge' => 'Judul Badge',
            'description' => 'Deskripsi',
            'object_type' => 'Tipe',
            'object_event' => 'Event',
            'total_hit' => 'Total Hit',
            'start_date' => 'Tanggal Mulai',
            'end_date' => 'Tanggal Berakhir',
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
