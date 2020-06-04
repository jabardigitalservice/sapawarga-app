<?php

namespace app\models;

use Jdsteam\Sapawarga\Models\Concerns\HasActiveStatus;
use Jdsteam\Sapawarga\Models\Concerns\HasArea;
use Jdsteam\Sapawarga\Models\Contracts\ActiveStatus;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use Illuminate\Support\Collection;

/**
 * This is the model class for table "beneficiaries".
 *
 * @property int $id
 * @property string $nik
 * @property string $name
 * @property string $kabkota_bps_id
 * @property string $kec_bps_id
 * @property string $kel_bps_id
 * @property string $kabkota_id
 * @property string $kec_id
 * @property string $kel_id
 * @property string $rt
 * @property string $rw
 * @property string $address
 * @property string $phone
 * @property int $total_family_members
 * @property string $job_type_id
 * @property string $job_status_id
 * @property int $income_before
 * @property int $income_after
 * @property string $image_ktp
 * @property string $image_kk
 * @property int $status_verification
 * @property int $status
 * @property string $notes
 * @property int $created_by
 * @property int $updated_by
 * @property int $created_at
 * @property int $updated_at
 */

class BeneficiaryAllocation extends ActiveRecord implements ActiveStatus
{
    use HasArea, HasActiveStatus;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'beneficiaries_allocation';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [
                ['nik', 'name', 'status'],
                'required',
            ],

            ['nik', 'unique'],

            [
                [
                    'name', 'address', 'phone', 'nik', 'no_kk', 'notes', 'rt', 'rw',
                    'kabkota_bps_id', 'kec_bps_id', 'kel_bps_id',
                ],
                'trim'
            ],
            [['rw', 'rt'], 'filter', 'filter' => function ($value) {
                return ltrim($value, '0');
            }],
            ['status', 'in', 'range' => [-1, 0, 10]],
        ];
    }

    public function fields()
    {
        $fields = [
            'id',
            'nik',
            'no_kk',
            'name',
            'kabkota_bps_id',
            'kec_bps_id',
            'kel_bps_id',
            'kabkota' => 'KabkotaBpsField',
            'kecamatan' => 'KecBpsField',
            'kelurahan' => 'KelBpsField',
            'rt',
            'rw',
            'address',
            'phone',
            'notes',
            'status',
            'created_at',
            'updated_at',
            'created_by',
            'updated_by',
        ];

        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'nik' => 'No KTP',
            'no_kk' => 'No KK',
            'name' => 'Nama Lengkap',
            'kabkota_bps_id' => 'Kota',
            'kec_bps_id' => 'Kecamatan',
            'kel_bps_id' => 'Kelurahan / Desa',
            'rt' => 'RT',
            'rw' => 'RW',
            'address' => 'Alamat',
            'phone' => 'Telepon',
            'status' => '',
            'notes' => 'Catatan',
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
