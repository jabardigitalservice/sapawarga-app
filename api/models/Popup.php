<?php

namespace app\models;

use app\validator\InputCleanValidator;
use Jdsteam\Sapawarga\Models\Concerns\HasActiveStatus;
use Jdsteam\Sapawarga\Models\Contracts\ActiveStatus;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "popup".
 *
 * @property int $id
 * @property string $title
 * @property string $image_path
 * @property string $type
 * @property string $link_url
 * @property int $internal_category
 * @property int $internal_entity_id
 * @property int $status
 * @property datetime $start_date
 * @property datetime $end_date
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */

class Popup extends ActiveRecord implements ActiveStatus
{
    use HasActiveStatus;

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
            [['title', 'image_path', 'type', 'status', 'start_date', 'end_date'],'required'],
            ['title', 'string', 'max' => 100],
            ['title', 'string', 'min' => 10],
            ['title', InputCleanValidator::class],
            [['title', 'image_path', 'type', 'link_url', 'internal_entity_name'], 'trim'],
            [['title', 'image_path', 'type', 'link_url', 'internal_entity_name'], 'safe'],

            [['start_date', 'end_date'], 'date', 'format' => 'php:Y-m-d H:i:s'],
            ['start_date', 'compare', 'compareAttribute' => 'end_date', 'operator' => '<'],
            ['end_date', 'compare', 'compareAttribute' => 'start_date', 'operator' => '>'],

            ['type', 'in', 'range' => ['internal', 'external']],
            ['type', 'validateTypeInternal'],
            ['type', 'validateTypeExternal'],

            ['link_url', 'url'],
            ['internal_category', 'in', 'range' => ['news', 'polling', 'survey']],
            [['status', 'internal_entity_id'], 'integer'],

            ['status', 'in', 'range' => [self::STATUS_DELETED, self::STATUS_DISABLED, self::STATUS_ACTIVE]],
        ];
    }

    public function fields()
    {
        $fields = [
            'id',
            'title',
            'image_path',
            'image_path_url' => function () {
                $publicBaseUrl = Yii::$app->params['storagePublicBaseUrl'];
                return "{$publicBaseUrl}/{$this->image_path}";
            },
            'type',
            'link_url',
            'internal_category',
            'internal_entity_id',
            'internal_entity_name',
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
            'image_path' => 'Image Path',
            'type' => 'Tipe',
            'link_url' => 'URL',
            'internal_category' => 'Internal kategori',
            'internal_entity_id' => 'Internal ID',
            'internal_entity_name' => 'Internal Entity Name',
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
            if (empty($this->internal_entity_id) && empty($this->internal_category)) {
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
}
