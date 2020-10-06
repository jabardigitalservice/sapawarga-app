<?php

namespace app\models;

use Jdsteam\Sapawarga\Models\Concerns\HasActiveStatus;
use Jdsteam\Sapawarga\Models\Concerns\HasArea;
use Jdsteam\Sapawarga\Models\Contracts\ActiveStatus;
use app\components\BeneficiaryHelper;
use Yii;
use app\validator\NikValidator;
use yii\base\DynamicModel;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use Illuminate\Support\Collection;

/**
 * This is the model class for table "beneficiaries".
 *
 * @property int $id
 * @property string $nik
 * @property string $nama_krt
 * @property string $kode_kab
 * @property string $kode_kec
 * @property string $kode_kel
 * @property string $rw
 * @property string $rt
 * @property string is_dtks
 * @property string id_tipe_bansos
 * @property string nama_kab
 * @property string nama_kec
 * @property string nama_kel
 * @property string no_kk
 * @property string alamat
 * @property string jumlah_art_tanggungan
 * @property string nomor_hp
 * @property string lapangan_usaha
 * @property string status_kedudukan
 * @property string penghasilan_sebelum_covid19
 * @property string penghasilan_setelah_covid
 * @property string keterangan
 * @property string id_manual
 * @property string id_sapawarga
 * @property string id_pikobar
 * @property string created_time
 * @property string updated_time
 * @property string deleted_time
 * @property bool is_deleted
 * @property string received_date
 * @property bool is_nik_valid
 * @property bool is_alamat_lengkap
 * @property bool is_manual
 * @property bool is_sapawarga
 * @property bool is_pikobar
 * @property bool is_super_clean
 * @property bool is_data_sisa
 */

class BeneficiaryBnbaTahapSatu extends ActiveRecord implements ActiveStatus
{
    use HasArea;
    use HasActiveStatus;

    public const STATUS_PENDING = 1;
    public const STATUS_REJECT = 2;
    public const STATUS_VERIFIED = 3;

    public const TYPE_PKH = 1;
    public const TYPE_BNPT = 2;
    public const TYPE_BANSOS = 3;
    public const TYPE_BANSOS_TUNAI = 4;
    public const TYPE_BANSOS_PRESIDEN_SEMBAKO = 5;
    public const TYPE_BANSOS_PROVINSI = 6;
    public const TYPE_DANA_DESA = 7;
    public const TYPE_BANSOS_KABKOTA = 8;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'beneficiaries_bnba_tahap_1';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['is_nik_valid', 'boolean'],
            ['is_alamat_lengkap', 'boolean'],
            ['is_manual', 'boolean'],
            ['is_sapawarga', 'boolean'],
            ['is_pikobar', 'boolean'],
            ['is_super_clean', 'boolean'],
            ['is_data_sisa', 'boolean'],
        ];
    }

    public function fields()
    {
        $fields = [
            'id',
            'is_nik_valid' => 'IsNIKValidField',
            'is_dtks',
            'id_tipe_bansos',
            'id_tipe_bansos_name' => 'bansosType',
            'nama_kab',
            'nama_kec',
            'nama_kel',
            'kode_kab',
            'kode_kec',
            'kode_kel',
            'rw',
            'rt',
            'nik',
            'nama_krt',
            'no_kk',
            'alamat',
            'jumlah_art_tanggungan',
            'nomor_hp',
            'lapangan_usaha',
            'lapangan_usaha_type' => 'jobTypeField',
            'status_kedudukan',
            'penghasilan_sebelum_covid19',
            'penghasilan_setelah_covid',
            'keterangan',
            'id_manual',
            'id_sapawarga',
            'id_pikobar',
            'created_time',
            'updated_time',
            'deleted_time',
            'is_deleted',
            'received_date',
            'is_nik_valid',
            'is_alamat_lengkap',
            'is_manual',
            'is_sapawarga',
            'is_pikobar',
            'is_super_clean',
            'is_data_sisa',
            'tahap_bantuan',
        ];

        return $fields;
    }

    public function getJobTypeField()
    {
        $configParams = include __DIR__ . '/../config/references/dinsos_job_types.php';
        $records = new Collection($configParams['job_field']);
        if ($this->lapangan_usaha === null) {
            return null;
        }

        return $records->where('id', '=', $this->lapangan_usaha)->first();
    }

    protected function getBansosType()
    {
        return BeneficiaryHelper::getBansosTypeList($this->id_tipe_bansos);
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
