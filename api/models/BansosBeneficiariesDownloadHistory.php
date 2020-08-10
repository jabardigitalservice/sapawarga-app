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
        'id' => 'beneficiaries.id',
        'kode_kab' => 'beneficiaries.domicile_kabkota_bps_id',
        'kode_kec' => 'beneficiaries.domicile_kec_bps_id',
        'kode_kel' => 'beneficiaries.domicile_kel_bps_id',
        'nama_kab' => 'a.name',
        'nama_kec' => 'a2.name',
        'nama_kel' => 'a3.name',
        'rt' => 'beneficiaries.domicile_rt',
        'rw' => 'beneficiaries.domicile_rw',
        'alamat'  => 'beneficiaries.domicile_address',
        'nama_krt' => 'beneficiaries.name',
        'nik' => 'beneficiaries.nik',
        'no_kk' => 'beneficiaries.no_kk',
        'jumlah_art_tanggungan' => 'beneficiaries.total_family_members',
        'nomor_hp' => 'beneficiaries.phone',
        'lapangan_usaha' => 'beneficiaries.job_type_id',
        'status_kedudukan' => 'beneficiaries.job_status_id',
        'penghasilan_sebelum_covid19' => 'beneficiaries.income_before',
        'penghasilan_setelah_covid' => 'beneficiaries.income_after',
        'keterangan' => 'beneficiaries.notes',
        'status_verifikasi' => 'beneficiaries.status_verification',
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
          ->leftJoin('areas a', 'beneficiaries.domicile_kabkota_bps_id = a.code_bps')
          ->leftJoin('areas a2', 'beneficiaries.domicile_kec_bps_id = a2.code_bps')
          ->leftJoin('areas a3', 'beneficiaries.domicile_kel_bps_id = a3.code_bps')
          ->where(['beneficiaries.status' => Beneficiary::STATUS_ACTIVE])
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
