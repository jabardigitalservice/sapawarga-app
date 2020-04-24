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

class Beneficiary extends ActiveRecord implements ActiveStatus
{
    use HasArea, HasActiveStatus;

    const STATUS_PENDING = 1;
    const STATUS_REJECT = 2;
    const STATUS_APPROVED = 3;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'beneficiaries';
    }

    public function getJobTypeField()
    {
        $configParams = include __DIR__ . '/../config/references/dinsos_job_types.php';
        $records = new Collection($configParams['job_field']);
        if ($this->job_type_id === null) {
            return null;
        }

        return $records->where('id', '=', $this->job_type_id)->first();
    }

    public function getJobStatusField()
    {
        $configParams = include __DIR__ . '/../config/references/dinsos_job_types.php';
        $records = new Collection($configParams['job_status']);
        if ($this->job_status_id === null) {
            return null;
        }

        return $records->where('id', '=', $this->job_status_id)->first();
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [
                ['nik', 'name', 'status_verification', 'status'],
                'required',
            ],

            ['nik', 'validateUniqueNIK'],

            [
                [
                    'name', 'address', 'phone', 'no_kk', 'notes', 'notes_approved', 'notes_rejected', 'image_ktp', 'image_kk', 'rt', 'rw',
                    'kabkota_bps_id', 'kec_bps_id', 'kel_bps_id',
                    'domicile_province_bps_id', 'domicile_kabkota_bps_id', 'domicile_kec_bps_id', 'domicile_kel_bps_id',
                    'domicile_rw', 'domicile_rt', 'domicile_address', 'nik'
                ],
                'trim'
            ],
            [['rw', 'rt', 'domicile_rw', 'domicile_rt'], 'filter', 'filter' => function ($value) {
                return ltrim($value, '0');
            }],
            [
                [
                    'status_verification', 'status', 'job_type_id', 'job_status_id',
                    'province_id', 'kabkota_id', 'kec_id', 'kel_id',
                    'income_before', 'income_after',
                    'is_poor_new', 'is_need_help',
                    'total_family_members',
                ],
                'integer'
            ],

            [['is_poor_new', 'is_need_help'], 'in', 'range' => [0, 1]],
            ['status_verification', 'in', 'range' => [1, 2, 3]],
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
            'province_bps_id',
            'kabkota_bps_id',
            'kec_bps_id',
            'kel_bps_id',
            'province_id',
            'kabkota_id',
            'kec_id',
            'kel_id',
            'province' => 'ProvBpsField',
            'kabkota' => 'KabkotaBpsField',
            'kecamatan' => 'KecBpsField',
            'kelurahan' => 'KelBpsField',
            'rt',
            'rw',
            'address',
            'domicile_province_bps_id',
            'domicile_kabkota_bps_id',
            'domicile_kec_bps_id',
            'domicile_kel_bps_id',
            'domicile_kabkota_name' => 'DomicileKabkotaField',
            'domicile_kec_name' => 'DomicileKecField',
            'domicile_kel_name' => 'DomicileKelField',
            'domicile_rt',
            'domicile_rw',
            'domicile_address',
            'phone',
            'total_family_members',
            'job_type_id',
            'job_status_id',
            'job_type_name' => 'jobTypeField',
            'job_status_name' => 'jobStatusField',
            'income_before',
            'income_after',
            'image_ktp',
            'image_kk',
            'image_ktp_url' => function () {
                $publicBaseUrl = Yii::$app->params['storagePublicBaseUrl'];
                return $this->image_ktp ? "$publicBaseUrl/$this->image_ktp" : null;
            },
            'image_kk_url' => function () {
                $publicBaseUrl = Yii::$app->params['storagePublicBaseUrl'];
                return $this->image_kk ? "$publicBaseUrl/$this->image_kk" : null;
            },
            'is_need_help',
            'is_poor_new',
            'notes',
            'notes_approved',
            'notes_rejected',
            'status_verification',
            'status_verification_label' => 'StatusLabelVerification',
            'status',
            'status_label' => 'StatusLabel',
            'created_at',
            'updated_at',
            'created_by',
        ];

        return $fields;
    }

    protected function getStatusLabelVerification()
    {
        $statusLabel = '';

        switch ($this->status_verification) {
            case self::STATUS_PENDING:
                $statusLabel = Yii::t('app', 'status.beneficiary.pending');
                break;
            case self::STATUS_REJECT:
                $statusLabel = Yii::t('app', 'status.beneficiary.reject');
                break;
            case self::STATUS_APPROVED:
                $statusLabel = Yii::t('app', 'status.beneficiary.approved');
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
            'total_family_members' => 'Total',
            'job_type_id' => 'Lapangan Usaha',
            'job_status_id' => 'Status Kedudukan',
            'income_before' => 'Penghasilan Sebelum',
            'income_after' => 'Penghasilan Sesudah',
            'image_ktp' => 'Foto KTP',
            'image_kk' => 'Foto KK',
            'status_verification' => 'Status Vefifikasi',
            'status' => '',
            'notes' => 'Catatan',
            'is_poor_new' => 'Warga Miskin Baru',
            'is_need_help' => 'Butuh Bantuan',
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
     * Checks if NIK is unique (doesn't exist in database)
     *
     * @param $attribute
     * @param $params
     */
    public function validateUniqueNIK($attribute, $params)
    {
        $beneficiary = Beneficiary::find()
            ->where(['nik' => $this->nik])
            ->andWhere(['!=', 'id', $this->id])
            ->exists();

        if ($beneficiary) {
            $this->addError($attribute, Yii::t('app', 'error.nik.taken'));
        }
    }
}
