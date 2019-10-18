<?php

namespace app\models;

use app\validator\InputCleanValidator;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "popup".
 *
 * @property int $id
 * @property string $title
 * @property string $description
 * @property string $image_path
 * @property string $type
 * @property string $link_url
 * @property int $internal_object_type
 * @property int $internal_object_id
 * @property int $status
 * @property datetime $start_date
 * @property datetime $end_date
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */

class Popup extends ActiveRecord
{
    const STATUS_DELETED = -1;
    const STATUS_ACTIVE = 10;
    const STATUS_STARTED = 15;

    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'popups';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'image_path', 'type', 'start_date', 'end_date', 'description'],'required'],
            ['title', 'string', 'max' => 100],
            ['title', 'string', 'min' => 10],
            ['title', InputCleanValidator::class],
            [['title', 'image_path', 'type', 'link_url', 'internal_object_name'], 'trim'],
            [['title', 'image_path', 'type', 'link_url', 'internal_object_name'], 'safe'],

            [['start_date', 'end_date'], 'date', 'format' => 'php:Y-m-d H:i:s'],
            ['start_date', 'compare', 'compareAttribute' => 'end_date', 'operator' => '<'],
            ['end_date', 'compare', 'compareAttribute' => 'start_date', 'operator' => '>'],

            ['type', 'in', 'range' => ['internal', 'external']],
            ['type', 'validateTypeInternal'],
            ['type', 'validateTypeExternal'],

            [['start_date', 'end_date'], 'validateRangeDate', 'on' => 'create'],
            [['start_date', 'end_date'], 'validateRangeDateNotMe', 'on' => 'update'],

            ['link_url', 'url'],
            ['internal_object_type', 'in', 'range' => ['news', 'polling', 'survey']],
            [['status', 'internal_object_id'], 'integer'],

            ['status', 'in', 'range' => [self::STATUS_DELETED, self::STATUS_ACTIVE]],
        ];
    }

    public function fields()
    {
        $fields = [
            'id',
            'title',
            'description',
            'image_path',
            'image_path_url' => function () {
                $publicBaseUrl = Yii::$app->params['storagePublicBaseUrl'];
                return "{$publicBaseUrl}/{$this->image_path}";
            },
            'type',
            'link_url',
            'internal_object_type',
            'internal_object_id',
            'internal_object_name',
            'status',
            'status_label' => 'StatusLabel',
            'start_date',
            'end_date',
            'created_at',
            'updated_at',
            'created_by',
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
            'description' => 'Deskripsi',
            'image_path' => 'Image Path',
            'type' => 'Tipe',
            'link_url' => 'URL',
            'internal_object_type' => 'Internal Object Type',
            'internal_object_id' => 'Internal Object ID',
            'internal_object_name' => 'Internal Object Name',
            'start_date' => 'Waktu Mulai',
            'end_date' => 'Waktu Berakhir',
            'status' => 'Status',
        ];
    }

    /** @inheritdoc */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => time(),
            ],
            BlameableBehavior::class,
        ];
    }

    public function validateTypeInternal($attribute, $params)
    {
        if ($this->type === 'internal') {
            if (empty($this->internal_object_id) && empty($this->internal_object_type)) {
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

    public function validateRangeDate($attribute, $params)
    {
        $checkExist = $this->checkExistRangeDate()->one();

        if (! empty($checkExist)) {
            $this->addError($attribute, Yii::t('app', 'error.validation.rangedatefill'));
        }
    }

    public function validateRangeDateNotMe($attribute, $params)
    {
        $checkExist = $this->checkExistRangeDate()
                ->andWhere(['not in', 'id', $this->id])
                ->one();

        if (! empty($checkExist)) {
            $this->addError($attribute, Yii::t('app', 'error.validation.rangedatefill'));
        }
    }

    public function checkExistRangeDate()
    {
        $query = Popup::find()
            ->where(['<>', 'status', Popup::STATUS_DELETED])
            ->andWhere([
                'and',
                ['<=', 'start_date', $this->end_date],
                ['>=', 'end_date', $this->start_date],
            ]);

        return $query;
    }
}
