<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Json;
use yii\db\Query;
use Jdsteam\Sapawarga\Jobs\ExportBeneficiariesJob;

/**
 * This is the model class for table "bansos_bnba_download_histories".
 *
 * {@inheritdoc}
 */
class BansosBeneficiariesDownloadHistory extends BaseDownloadHistory
{
    const TYPE_VERVAL = 'verval'; // original ExportBnba job type

    public $columns = [
        'id' => 'id',
        'kode_kab' => 'domicile_kabkota_bps_id',
        'kode_kec' => 'domicile_kec_bps_id',
        'kode_kel' => 'domicile_kel_bps_id',
        'nama_kab' => 'domicile_kabkota_name',
        'nama_kec' => 'domicile_kec_name',
        'nama_kel' => 'domicile_kel_name',
        'rt' => 'domicile_rt',
        'rw' => 'domicile_rw',
        'alamat'  => 'domicile_address',
        'nama_krt' => 'name',
        'nik' => 'nik',
        'no_kk' => 'no_kk',
        'jumlah_art_tanggungan' => 'total_family_members',
        'nomor_hp' => 'phone',
        'lapangan_usaha' => 'job_type_id',
        'status_kedudukan' => 'job_status_id',
        'penghasilan_sebelum_covid19' => 'income_before',
        'penghasilan_setelah_covid' => 'income_after',
        'keterangan' => 'notes',
        'status_verifikasi' => 'status_verification',
    ];

    /** Count affected rows in this queue job
     *
     * @return int Number of affected rows
     */
    public function getQuery()
    {
        return (new Query())
          ->select($this->columns)
          ->from('beneficiaries')
          ->where(['status' => Beneficiary::STATUS_ACTIVE])
          ->andWhere($this->params)
          ;
    }

    /*
     * {@inheritdoc}
     */
    public function countAffectedRows()
    {
        return Beneficiary::find()
          ->where(['status' => Beneficiary::STATUS_ACTIVE])
          ->andWhere($this->params)
          ->count();
    }

    /** Start Export Verval Job according to type
     *
     * @return None
     */
    public function startJob()
    {
        $job_id = Yii::$app->queue->push(new ExportBeneficiariesJob([
            'userId' => $this->user_id,
            'historyId' => $this->id,
        ]));

        $logs = ($this->logs) ?: [];
        $logs['job_id'] = $job_id;
        $this->logs = $logs;
        $this->save();
    }
}
