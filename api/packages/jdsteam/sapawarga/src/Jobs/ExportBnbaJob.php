<?php

namespace Jdsteam\Sapawarga\Jobs;

use Yii;
use yii\base\BaseObject;
use yii\queue\RetryableJobInterface;
use app\models\BeneficiaryBnbaTahapSatu;
use app\models\User;
use app\models\BansosBnbaDownloadHistory;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use League\Flysystem\AdapterInterface;

class ExportBnbaJob extends BaseObject implements RetryableJobInterface
{
    public $params;
    public $user_id;
    public $history_id;

    public function execute($queue)
    {
        \Yii::$app->language = 'id-ID';

        $job_history = BansosBnbaDownloadHistory::findOne($this->history_id);
        $job_history->start_at = time();
        $job_history->save();

        // size of query batch size used during database retrieval
        $batch_size = 1000;
        echo "Params: ";
        print_r($this->params);

        $query = BeneficiaryBnbaTahapSatu::find()->where($this->params);

        $row_numbers = $query->count();
        echo "Number of rows to be processed : $row_numbers" . PHP_EOL;

        echo "Starting generating BNBA list export\n" ;

        /* Generate export file using box/spout library.
         * ref: https://opensource.box.com/spout/getting-started/#writer */
        $writer = WriterEntityFactory::createXLSXWriter();
        
        // Initial varieble location, filename, path
        $now_date = date('Y-m-d-H-i-s');
        $file_name = "export-bnba-tahap-1-$now_date.xlsx";
        $file_path_temp = Yii::getAlias('@app/web') . '/storage/' . $file_name;

        $writer->openToFile($file_path_temp); // write data to a file or to a PHP stream

        $columns = [
            'id',
            'kode_kab',
            'kode_kec',
            'kode_kel',
            'nama_kab',
            'nama_kec',
            'nama_kel',
            'rt',
            'rw',
            'alamat',
            'nama_krt',
            'nik',
            'no_kk',
            'jumlah_art_tanggungan',
            'nomor_hp',
            'lapangan_usaha',
            'status_kedudukan',
            'penghasilan_sebelum_covid19',
            'penghasilan_setelah_covid',
            'keterangan',
        ];
        $column_headers = array_merge($columns, ['Pintu Bantuan']);
        $column_values = array_merge($columns, ['bansostype']);

        /** Shortcut: add a row from an array of values */
        $rowFromValues = WriterEntityFactory::createRowFromArray($column_headers);
        $writer->addRow($rowFromValues);

        // create unbuffered database connection to avoid MySQL batching limitation
        // ref: https://www.yiiframework.com/doc/guide/2.0/en/db-query-builder#batch-query-mysql
        $unbuffered_db = new \yii\db\Connection([
            'dsn' => Yii::$app->db->dsn,
            'username' => Yii::$app->db->username,
            'password' => Yii::$app->db->password,
            'charset' => Yii::$app->db->charset,
        ]);
        $unbuffered_db->open();
        $unbuffered_db->pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);

        $num_processed = 0;
        foreach ($query->batch($batch_size, $unbuffered_db) as $list_bnba)
        {
            $data = ArrayHelper::toArray($list_bnba, [
                'app\models\BeneficiaryBnbaTahapSatu' => $column_values,
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
        $unbuffered_db->close();

        $job_history->row_processed = $job_history->row_count;
        $job_history->done_at = time();
        $job_history->save();

        echo "Finished generating export file" . PHP_EOL;

        // upload to S3 & send notification email
        $relative_path = "export-bnba-list/$file_name";
        Yii::$app->queue->priority(10)->push(new UploadS3Job([
            'job_history_class_name' => 'app\models\BansosBnbaDownloadHistory',
            'relative_path' => $relative_path,
            'file_path_temp' => $file_path_temp,
            'user_id' => $this->user_id,
            'history_id' => $this->history_id,
            'email_notif_param' => [
                'template' => ['html' => 'email-result-export-list-bnba'],
                'subject' => 'Notifikasi dari Sapawarga: Hasil export daftar BNBA sudah bisa diunduh!',
            ],
        ]));

    }

    /* Time To Reserve property. 
     * ref: https://github.com/yiisoft/yii2-queue/blob/master/docs/guide/retryable.md#retry-options 
     * @return int Seconds
     */
    public function getTtr()
    {
        return 60 * 60;
    }

    /** wether current job is still retryable
     * ref: https://github.com/yiisoft/yii2-queue/blob/master/docs/guide/retryable.md#retry-options
     * @return boolean
     */
    public function canRetry($attempt, $error)
    {
        return ($attempt < 3);
    }
}
