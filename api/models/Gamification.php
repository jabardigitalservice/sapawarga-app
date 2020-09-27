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

    public const SCENARIO_CREATE = 'create';
    public const SCENARIO_UPDATE = 'update';

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
        $today = date('Y-m-d');

        return [
            ['title', 'string', 'min' => 10],
            ['title', InputCleanValidator::class],

            [['title','title_badge','image_badge_path','description','object_type','object_event',
                'total_hit','status','start_date','end_date'
            ], 'trim'
            ],

            [['title','title_badge','image_badge_path','description','object_type','object_event',
                'total_hit','status','start_date','end_date'
            ], 'safe'
            ],

            [['title','title_badge','image_badge_path','description','object_type','object_event',
                'total_hit','status','start_date','end_date'
            ], 'required'
            ],

            [['start_date', 'end_date'], 'date', 'format' => 'php:Y-m-d'],
            ['end_date','validateEndDate'],
            ['start_date', 'compare', 'compareAttribute' => 'end_date', 'operator' => '<'],
            ['end_date', 'compare', 'compareAttribute' => 'start_date', 'operator' => '>'],

            [['start_date', 'end_date'], 'validateRangeDate', 'on' => 'create'],
            [['start_date', 'end_date'], 'validateRangeDateNotMe', 'on' => 'update'],

            [['status', 'total_hit'], 'integer'],

            ['object_type', 'in', 'range' => ['news', 'news_important', 'user_post']],
            ['object_event', 'in', 'range' => ['news_view_detail', 'news_important_view_detail', 'user_post_create']],

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

    public function validateEndDate($attribute, $params)
    {
        $today = date('Y-m-d');

        if ($this->end_date <= $today) {
            $this->addError($attribute, Yii::t('app', 'error.validation.enddate_less_than_today'));
        }
    }

    public function validateRangeDate($attribute, $params)
    {
        $checkExist = $this->checkExistRangeDate()->exists();

        if (! empty($checkExist)) {
            $this->addError($attribute, Yii::t('app', 'error.validation.rangedatefill'));
        }
    }

    public function validateRangeDateNotMe($attribute, $params)
    {
        $checkExist = $this->checkExistRangeDate()
                ->andWhere(['not in', 'id', $this->id])
                ->exists();

        if ($checkExist) {
            $this->addError($attribute, Yii::t('app', 'error.validation.rangedatefill'));
        }
    }

    public function checkExistRangeDate()
    {
        $checkRangeDate = Gamification::find()
            ->where(['status' => Gamification::STATUS_ACTIVE])
            ->andWhere([
                'and',
                ['<=', 'start_date', $this->end_date],
                ['>=', 'end_date', $this->start_date],
            ])
            ->andWhere(['object_type' => $this->object_type])
            ->andWhere(['object_event' => $this->object_event]);

        return $checkRangeDate;
    }
}
