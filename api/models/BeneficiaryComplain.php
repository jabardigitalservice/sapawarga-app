<?php

namespace app\models;

use app\components\ModelHelper;
use app\validator\InputCleanValidator;
use Jdsteam\Sapawarga\Models\Concerns\HasActiveStatus;
use Jdsteam\Sapawarga\Models\Contracts\ActiveStatus;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "beneficiaries".
 *
 * @property int $id
 * @property string $beneficiaries_id
 * @property string $name
 * @property string $phone
 * @property string $kabkota_bps_id
 * @property string $kec_bps_id
 * @property string $kel_bps_id
 * @property string $kabkota_bps_name
 * @property string $kec_bps_name
 * @property string $kel_bps_name
 * @property string $kec_id
 * @property string $kel_id
 * @property string $rt
 * @property string $rw
 * @property int $status
 * @property string $notes_reason
 * @property int $created_by
 * @property int $updated_by
 * @property int $created_at
 * @property int $updated_at
 */

class BeneficiaryComplain extends ActiveRecord implements ActiveStatus
{
    use HasActiveStatus;


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'beneficiaries_complain';
    }

    public function getBeneficiary()
    {
        return $this->hasOne(Beneficiary::class, ['id' => 'beneficiaries_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [
                ['beneficiaries_id', 'name', 'nik', 'phone'],
                'required'
            ],

            [
                ['name', 'phone', 'kabkota_bps_id', 'kec_bps_id', 'kel_bps_id', 'kabkota_bps_name', 'kec_bps_name', 'kel_bps_name', 'address', 'rt','rw', 'notes_reason'],
                'trim'
            ],

            [
                ['nik', 'kabkota_bps_id', 'kec_bps_id', 'kel_bps_id', 'rt', 'rw'],
                'integer'
            ],

            ['status', 'in', 'range' => [-1, 0, 10]],
        ];
    }

    public function fields()
    {
        $fields = [
            'id',
            'beneficiaries_id',
            'beneficiary' => 'beneficiary',
            'nik',
            'name',
            'phone',
            'kabkota_bps_id',
            'kec_bps_id',
            'kel_bps_id',
            'kabkota_bps_name',
            'kec_bps_name',
            'kel_bps_name',
            'address',
            'rt',
            'rw',
            'notes_reason',
            'status',
            'status_label' => 'StatusLabel',
        ];

        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'beneficiaries_id' => 'Id Bansos',
            'name' => 'Nama Lengkap',
            'phone' => 'No Telepon',
            'kabkota_bps_id' => 'Kota',
            'kec_bps_id' => 'Kecamatan',
            'kel_bps_id' => 'Kelurahan / Desa',
            'address' => 'Alamat',
            'rt' => 'RT',
            'rw' => 'RW',
            'notes_reason' => 'Alasan Pengaduan',
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
            ]
        ];
    }
}
