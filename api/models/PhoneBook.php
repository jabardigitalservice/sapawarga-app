<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "phonebooks".
 *
 * @property int $id
 * @property string $name
 * @property string $address
 * @property string $description
 * @property mixed $phone_numbers
 * @property int $kabkota_id
 * @property int $kec_id
 * @property int $kel_id
 * @property string $latitude
 * @property string $longitude
 * @property int $seq
 * @property string $cover_image_path
 * @property mixed $meta
 * @property int $status
 */
class PhoneBook extends \yii\db\ActiveRecord
{
    const STATUS_DELETED = -1;
    const STATUS_DISABLED = 0;
    const STATUS_ACTIVE = 10;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'phonebooks';
    }

    public function getCategory()
    {
        return $this->hasOne(Category::class, ['id' => 'category_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['name', 'string', 'max' => 64],
            [['name'], 'trim'],
            [['name', 'phone_numbers', 'seq', 'status'], 'required'],
            [['category_id', 'kabkota_id', 'kec_id', 'kel_id', 'seq'], 'integer'],
        ];
    }

    public function fields()
    {
        $fields = [
            'id',
            'name',
            'category_id',
            'category',
            'address',
            'description',
            'phone_numbers',
            'kabkota_id',
            'kec_id',
            'kel_id',
            'latitude',
            'longitude',
            'seq',
            'cover_image_path',
            'meta',
            'status',
            'status_label' => function () {
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
            },
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
            'id'   => 'ID',
            'name' => 'Nama',
        ];
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
        ];
    }
}
