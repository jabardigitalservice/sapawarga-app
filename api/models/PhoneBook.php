<?php

namespace app\models;

use Jdsteam\Sapawarga\Behaviors\AreaBehavior;
use Jdsteam\Sapawarga\Models\Concerns\HasActiveStatus;
use Jdsteam\Sapawarga\Models\Concerns\HasArea;
use Jdsteam\Sapawarga\Models\Concerns\HasCategory;
use Jdsteam\Sapawarga\Models\Contracts\ActiveStatus;
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
 * @property int $category_id
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
class PhoneBook extends \yii\db\ActiveRecord implements ActiveStatus
{
    use HasActiveStatus;
    use HasArea;
    use HasCategory;

    public const CATEGORY_TYPE = 'phonebook';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'phonebooks';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['name', 'string', 'max' => 64],

            [['name', 'address', 'description'], 'trim'],
            [['address', 'description', 'latitude', 'longitude', 'cover_image_path', 'meta'], 'default'],

            [['name', 'category_id', 'phone_numbers', 'seq', 'status'], 'required'],
            [['kabkota_id', 'kec_id', 'kel_id', 'seq'], 'integer'],
        ];
    }

    public function fields()
    {
        $fields = [
            'id',
            'name',
            'category_id',
            'category' => 'CategoryField',
            'address',
            'description',
            'phone_numbers',
            'kabkota_id',
            'kabkota' => 'KabkotaField',
            'kec_id',
            'kecamatan' => 'KecamatanField',
            'kel_id',
            'kelurahan' => 'KelurahanField',
            'latitude',
            'longitude',
            'seq',
            'cover_image_path',
            'cover_image_url' => function () {
                $publicBaseUrl = Yii::$app->params['storagePublicBaseUrl'];

                return $this->cover_image_path ? "$publicBaseUrl/$this->cover_image_path" : null;
            },
            'meta',
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
            [
                'class'  => AreaBehavior::class,
                'withRw' => false,
            ],
        ];
    }
}
