<?php

namespace Jdsteam\Sapawarga\Jobs;

use Yii;
use yii\base\BaseObject;
use yii\queue\RetryableJobInterface;
use yii\db\Query;
use app\models\Beneficiary;
use app\models\User;
use app\models\BansosBeneficiariesDownloadHistory;
use yii\helpers\ArrayHelper;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use League\Flysystem\AdapterInterface;

class ExportBeneficiariesJob extends BaseObject implements RetryableJobInterface
{
    public $params;
    public $user_id;
    public $history_id;

    public function execute($queue)
    {
        $job_history = BansosBeneficiariesDownloadHistory::findOne($this->history_id);
        $job_history->start_at = time();
        $job_history->save();

        // size of query batch size used during database retrieval
        $batch_size = 1000;

        echo "Params:". PHP_EOL;
        print_r($this->params);

        $columns = [
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
        ];

        $query = (new Query())
          ->select($columns)
          ->from('beneficiaries')
          ->leftJoin('areas a', 'beneficiaries.domicile_kabkota_bps_id = a.code_bps')
          ->leftJoin('areas a2', 'beneficiaries.domicile_kec_bps_id = a2.code_bps')
          ->leftJoin('areas a3', 'beneficiaries.domicile_kel_bps_id = a3.code_bps')
          ->where($this->params)
          ->andWhere(['not', ['status_verification' => 2]])
          ;

        $row_numbers = $query->count();
        echo "Number of rows to be processed : $row_numbers" . PHP_EOL;

        echo "Starting generating BNBA list export\n" ;

        /* Generate export file using box/spout library.
         * ref: https://opensource.box.com/spout/getting-started/#writer */
        $writer = WriterEntityFactory::createXLSXWriter();
        
        // Initial varieble location, filename, path
        $now_date = date('Y-m-d-H-i-s');
        $file_name = "export-calon-penerima-bantuan-$now_date.xlsx";
        $file_path_temp = Yii::getAlias('@app/web') . '/storage/' . $file_name;

        $writer->openToFile($file_path_temp); // write data to a file or to a PHP stream
        /** Shortcut: add a row from an array of values */
        $rowFromValues = WriterEntityFactory::createRowFromArray(array_keys($columns));
        $writer->addRow($rowFromValues);

        // create unbuffered database connection to avoid MySQL batching limitation
        // ref: https://www.yiiframework.com/doc/guide/2.0/en/db-query-builder#batch-query-mysql
        $unbeffered_db = new \yii\db\Connection([
            'dsn' => Yii::$app->db->dsn,
            'username' => Yii::$app->db->username,
            'password' => Yii::$app->db->password,
            'charset' => Yii::$app->db->charset,
        ]);
        $unbeffered_db->open();
        $unbeffered_db->pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);

        $num_processed = 0;
        foreach ($query->batch($batch_size, $unbeffered_db) as $list_bnba)
        {
            $data = ArrayHelper::toArray($list_bnba, [
                'app\models\BeneficiaryBnbaTahapSatu' => $columns,
            ]);

            foreach ($data as $row) {
                $rowFromValues = WriterEntityFactory::createRowFromArray($row);
                $writer->addRow($rowFromValues);
            }

            $num_processed += count($data);
            echo sprintf("Processed : %d/%d (%.2f%%)\n", $num_processed, $row_numbers, ($num_processed*100/$row_numbers));

            $job_history->row_processed = $num_processed;
            $job_history->save();
        }

        $writer->close();
        $unbeffered_db->close();

        $job_history->row_processed = $job_history->row_count;
        $job_history->done_at = time();
        $job_history->save();

        echo "Finished generating export file" . PHP_EOL;

        // upload to S3 & send notification email
        $relative_path = "export-beneficiaries-list/$file_name";
        Yii::$app->queue->priority(10)->push(new UploadS3Job([
            'job_history_class_name' => 'app\models\BansosBeneficiariesDownloadHistory',
            'relative_path' => $relative_path,
            'file_path_temp' => $file_path_temp,
            'user_id' => $this->user_id,
            'history_id' => $this->history_id,
            'email_notif_param' => [
                'template' => ['html' => 'email-result-export-list-beneficiaries'],
                'subject' => 'Notifikasi dari Sapawarga: Hasil export Daftar Calon Penerima Bantuan sudah bisa diunduh!',
            ],
        ]));

    }

    public function getTtr()
    {
        return 60 * 60;
    }

    public function canRetry($attempt, $error)
    {
        return ($attempt < 3);
    }
}
