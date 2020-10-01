<?php

namespace app\models\pub;

use app\components\ModelHelper;
use app\components\BeneficiaryHelper;
use app\validator\InputCleanValidator;
use Jdsteam\Sapawarga\Models\Concerns\HasArea;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use Illuminate\Support\Collection;

/**
 * This is the model class for table "beneficiaries_bnba_tahap_1".
 *
 * @property int $id
 * @property string $nik
 * @property string $nama_krt
 * @property string $lapangan_usaha
 * @property string $rt
 * @property string $rw
 * @property string $id_tipe_bansos
 */

class BeneficiaryBnba extends ActiveRecord
{
    use HasArea;

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
            [
                ['nik', 'name', 'status_verification', 'status'],
                'required',
            ],

            [['nik'], 'unique'],

            [
                ['name', 'address', 'phone', 'no_kk', 'notes', 'image_ktp', 'image_kk'],
                'trim'
            ],

            [
                ['status_verification', 'status', 'job_type_id', 'job_status_id', 'kabkota_bps_id', 'kec_bps_id', 'kel_bps_id', 'kabkota_id', 'kec_id', 'kel_id', 'income_before', 'income_after', 'total_family_members', 'rt', 'rw'],
                'integer'
            ],

            ['status_verification', 'in', 'range' => [1, 2, 3]],
            ['status', 'in', 'range' => [-1, 0, 10]],
        ];
    }

    public function getJobTypeField()
    {
        $configParams = include __DIR__ . '/../../config/references/dinsos_job_types.php';
        $records = new Collection($configParams['job_field']);
        if ($this->lapangan_usaha === null) {
            return null;
        }

        return $records->where('id', '=', $this->lapangan_usaha)->first();
    }

    public function getJobStatusField()
    {
        $configParams = include __DIR__ . '/../../config/references/dinsos_job_types.php';
        $records = new Collection($configParams['job_status']);
        if ($this->job_status_id === null) {
            return null;
        }

        return $records->where('id', '=', $this->job_status_id)->first();
    }

    public function fields()
    {
        $fields = [
            'id',
            'nama_masking' => 'nameMasking',
            'nik' => 'nikMasking',
            'lapangan_usaha' => 'jobTypeField',
            'nama_kab',
            'nama_kec',
            'nama_kel',
            'rt',
            'rw',
            'alamat' => 'addressMasking',
            'id_tipe_bansos',
            'id_tipe_bansos_name' => 'bansosType',
            'tahap_bantuan',
        ];

        return $fields;
    }

    protected function getNikMasking()
    {
        return BeneficiaryHelper::getNikMasking($this->nik);
    }

    protected function getNameMasking()
    {
        return BeneficiaryHelper::getNameMasking($this->nama_krt);
    }

    protected function getAddressMasking()
    {
        return BeneficiaryHelper::getNameMasking($this->alamat);
    }

    protected function getBansosType()
    {
        return BeneficiaryHelper::getBansosTypeList($this->id_tipe_bansos);
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
