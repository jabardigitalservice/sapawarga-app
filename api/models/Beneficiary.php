<?php

namespace app\models;

use Jdsteam\Sapawarga\Models\Concerns\HasActiveStatus;
use Jdsteam\Sapawarga\Models\Concerns\HasArea;
use Jdsteam\Sapawarga\Models\Contracts\ActiveStatus;
use Yii;
use app\validator\NikValidator;
use yii\base\DynamicModel;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * This is the model class for table "beneficiaries".
 *
 * @property int $id
 * @property string $nik
 * @property string $name
 * @property string $domicile_province_bps_id
 * @property string $domicile_kabkota_bps_id
 * @property string $domicile_kec_bps_id
 * @property string $domicile_kel_bps_id
 * @property string $domicile_rw
 * @property string $domicile_rt
 * @property string $domicile_address
 * @property string $phone
 * @property int $total_family_members
 * @property string $job_type_id
 * @property string $job_status_id
 * @property int $income_before
 * @property int $income_after
 * @property string $image_ktp
 * @property string $image_kk
 * @property int $is_need_help
 * @property int $is_poor_new
 * @property int $status_verification
 * @property int $status
 * @property string $notes
 * @property string $notes_approved
 * @property string $notes_rejected
 * @property string $notes_nik_empty
 * @property int $created_by
 * @property int $updated_by
 * @property int $created_at
 * @property int $updated_at
 */

class Beneficiary extends ActiveRecord implements ActiveStatus
{
    use HasArea, HasActiveStatus;

    // Status ids for verification
    const STATUS_PENDING = 1;
    const STATUS_REJECT = 2;
    const STATUS_VERIFIED = 3;

    // Status ids for approval
    const STATUS_REJECTED_KEL = 4;
    const STATUS_APPROVED_KEL = 5;
    const STATUS_REJECTED_KEC = 6;
    const STATUS_APPROVED_KEC = 7;
    const STATUS_REJECTED_KABKOTA = 8;
    const STATUS_APPROVED_KABKOTA = 9;

    // Action names for approval
    const ACTION_APPROVE = 'APPROVE';
    const ACTION_REJECT = 'REJECT';

    // Types used on Dashboards
    const TYPE_PROVINSI = 'provinsi';
    const TYPE_KABKOTA = 'kabkota';
    const TYPE_KEC = 'kec';
    const TYPE_KEL = 'kel';
    const TYPE_RW = 'rw';

    // Label for status_verification
    const STATUS_VERIFICATION_LABEL = [
        self::STATUS_PENDING => 'status.beneficiary.pending',
        self::STATUS_REJECT => 'status.beneficiary.reject',
        self::STATUS_VERIFIED => 'status.beneficiary.verified',
        self::STATUS_REJECTED_KEL => 'status.beneficiary.rejected_kel',
        self::STATUS_APPROVED_KEL => 'status.beneficiary.approved_kel',
        self::STATUS_REJECTED_KEC => 'status.beneficiary.rejected_kec',
        self::STATUS_APPROVED_KEC => 'status.beneficiary.approved_kec',
        self::STATUS_REJECTED_KABKOTA => 'status.beneficiary.rejected_kabkota',
        self::STATUS_APPROVED_KABKOTA => 'status.beneficiary.approved_kabkota',
    ];

    // Constants for Scenario names
    const SCENARIO_VALIDATE_ADDRESS = 'validate-address';
    const SCENARIO_VALIDATE_NIK = 'validate-nik';

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
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $attributeNik = ['id', 'nik'];
        $attributesAddress = [
            'name',
            'domicile_kabkota_bps_id',
            'domicile_kec_bps_id',
            'domicile_kel_bps_id',
            'domicile_rt',
            'domicile_rw',
            'domicile_address',
        ];

        $scenarios[self::SCENARIO_VALIDATE_ADDRESS] = $attributesAddress;
        $scenarios[self::SCENARIO_VALIDATE_NIK] = $attributeNik;
        return $scenarios;
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = [
            [
                [
                    'name',
                    'domicile_kabkota_bps_id',
                    'domicile_kec_bps_id',
                    'domicile_kel_bps_id',
                    'domicile_rt',
                    'domicile_rw',
                    'domicile_address',
                    'status_verification',
                    'status',
                ],
                'required',
            ],

            [
                [
                    'name', 'address', 'phone', 'no_kk', 'notes', 'notes_approved', 'notes_rejected', 'notes_nik_empty', 'image_ktp', 'image_kk', 'rt', 'rw',
                    'kabkota_bps_id', 'kec_bps_id', 'kel_bps_id',
                    'domicile_province_bps_id', 'domicile_kabkota_bps_id', 'domicile_kec_bps_id', 'domicile_kel_bps_id',
                    'domicile_rw', 'domicile_rt', 'domicile_address', 'nik'
                ],
                'trim'
            ],
            ['name', 'string', 'length' => [2, 100]],
            [
                'name', 'unique', 'targetAttribute'=> ['name', 'domicile_address'],
                'message' => Yii::t('app', 'error.address.duplicate'),
                'on' => self::SCENARIO_VALIDATE_ADDRESS
            ],
            [
                [
                    'nik', 'address', 'phone', 'no_kk', 'notes', 'notes_approved', 'notes_rejected', 'notes_nik_empty', 'image_ktp', 'image_kk',
                    'kabkota_bps_id', 'kec_bps_id', 'kel_bps_id',
                    'domicile_province_bps_id'
                ],
                'default', 'value' => null
            ],
            [['rw', 'rt', 'domicile_rw', 'domicile_rt'], 'filter', 'filter' => function ($value) {
                $trimmed = ltrim($value, '0');
                return $trimmed ? $trimmed : null;
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
            ['status_verification', 'in', 'range' => [
                self::STATUS_PENDING,
                self::STATUS_REJECT,
                self::STATUS_VERIFIED,
                self::STATUS_REJECTED_KEL,
                self::STATUS_APPROVED_KEL,
                self::STATUS_REJECTED_KEC,
                self::STATUS_APPROVED_KEC,
                self::STATUS_REJECTED_KABKOTA,
                self::STATUS_APPROVED_KABKOTA,
            ]],
            ['status', 'in', 'range' => [-1, 0, 10]],
        ];

        return array_merge(
            $rules,
            $this->rulesNik(),
            $this->rulesKk(),
            $this->rulesAddress()
        );
    }

    protected function rulesNik()
    {
        return [
            [ 'nik', 'required', 'on' => self::SCENARIO_VALIDATE_NIK ],
            [
                'nik', 'unique',
                'filter' => function ($query) {
                    // var_dump($this->id);
                    $query->andWhere(['!=', 'status', Beneficiary::STATUS_DELETED])
                          ->andFilterWhere(['!=', 'id', $this->id]);
                },
                'message' => Yii::t('app', 'error.nik.taken'),
                'on' => self::SCENARIO_VALIDATE_NIK
            ],
        ];
    }

    protected function rulesKk()
    {
        return [];
    }

    protected function rulesAddress()
    {
        return [];
    }

    public function fields()
    {
        $fields = [
            'id',
            'nik',
            'is_nik_valid' => 'IsNIKValidField',
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
            'notes_nik_empty',
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
        $authUser = Yii::$app->user;
        $authUserModel = $authUser->identity;
        $localizationKey = null;

        if (!$this->status_verification) {
            return '';
        }

        switch ($authUserModel->role) {
            case User::ROLE_TRAINER:
            case User::ROLE_STAFF_RW:
                // When status_verification >= 3, status label for staffRW does not change
                if ($this->status_verification >= self::STATUS_VERIFIED) {
                    $localizationKey = self::STATUS_VERIFICATION_LABEL[self::STATUS_VERIFIED];
                } else {
                    $localizationKey = self::STATUS_VERIFICATION_LABEL[$this->status_verification];
                }
                break;
            // Handle special cases for staffKec and staffKabkota
            case User::ROLE_STAFF_KEC:
                if ($this->status_verification == self::STATUS_APPROVED_KEL) {
                    $localizationKey = 'status.beneficiary.pending_kec';
                } else {
                    $localizationKey = self::STATUS_VERIFICATION_LABEL[$this->status_verification];
                }
                break;
            case User::ROLE_STAFF_KABKOTA:
                if ($this->status_verification == self::STATUS_APPROVED_KEC) {
                    $localizationKey = 'status.beneficiary.pending_kabkota';
                } else {
                    $localizationKey = self::STATUS_VERIFICATION_LABEL[$this->status_verification];
                }
                break;
            default:
                $localizationKey = self::STATUS_VERIFICATION_LABEL[$this->status_verification];
                break;
        }

        return Yii::t('app', $localizationKey);
    }

    protected function getIsNIKValidField()
    {
        $nikModel = new DynamicModel(['nik' => $this->nik]);
        $nikModel->addRule('nik', 'trim');
        $nikModel->addRule('nik', 'required');
        $nikModel->addRule('nik', NikValidator::class);

        return (int)$nikModel->validate();
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'nik' => 'NIK',
            'no_kk' => 'No KK',
            'name' => 'Nama Lengkap',
            'domicile_kabkota_bps_id' => 'Kabupaten/Kota',
            'domicile_kec_bps_id' => 'Kecamatan',
            'domicile_kel_bps_id' => 'Desa/Kelurahan',
            'domicile_rt' => 'RT',
            'domicile_rw' => 'RW',
            'domicile_address' => 'Alamat',
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
}
