<?php

namespace Jdsteam\Sapawarga\Jobs;

use Yii;
use yii\base\BaseObject;
use yii\queue\RetryableJobInterface;
use app\models\BeneficiaryBnbaTahapSatu;
use app\models\User;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use League\Flysystem\AdapterInterface;
use Jdsteam\Sapawarga\Jobs\Concerns\HasJobHistory;

class ExportBnbaJob extends BaseObject implements RetryableJobInterface
{
    use HasJobHistory;

    public $params;
    public $userId;

    public function execute($queue)
    {
        \Yii::$app->language = 'id-ID';

        $this->jobHistoryClassName = 'app\models\BansosBnbaDownloadHistory';
        $jobHistory = $this->jobHistory;
        $jobHistory->start_at = time();
        $jobHistory->save();

        // size of query batch size used during database retrieval
        $batch_size = 1000;
        echo "Params: ";
        print_r($this->params);

        $query = $jobHistory->getQuery();

        $row_numbers = $query->count();
        echo "Number of rows to be processed : $row_numbers" . PHP_EOL;

        echo "Starting generating BNBA list export\n" ;

        /* Generate export file using box/spout library.
         * ref: https://opensource.box.com/spout/getting-started/#writer */
        $writer = WriterEntityFactory::createXLSXWriter();
        
        // Initial varieble location, filename, path
        $now_date = date('Y-m-d-H-i-s');
        $fileName = "export-bnba-tahap-1-$now_date.xlsx";
        $filePathTemp = Yii::getAlias('@app/web') . '/storage/' . $fileName;

        $writer->openToFile($filePathTemp); // write data to a file or to a PHP stream

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

            $jobHistory->row_processed = $num_processed;
            $jobHistory->save();
        }

        $writer->close();
        $unbuffered_db->close();

        $jobHistory->row_processed = $jobHistory->row_count;
        $jobHistory->done_at = time();
        $jobHistory->save();

        echo "Finished generating export file" . PHP_EOL;

        // upload to S3 & send notification email
        $relativePath = "export-bnba-list/$fileName";
        Yii::$app->queue->priority(10)->push(new UploadS3Job([
            'jobHistoryClassName' => 'app\models\BansosBnbaDownloadHistory',
            'relativePath' => $relativePath,
            'filePathTemp' => $filePathTemp,
            'userId' => $this->userId,
            'historyId' => $this->historyId,
            'emailNotifParam' => [
                'template' => ['html' => 'email-result-export-list-bnba'],
                'subject' => 'Notifikasi dari Sapawarga: Hasil export daftar BNBA sudah bisa diunduh!',
            ],
        ]));

    }

    /**
     * {@inheritdoc}
     */
    public function getTtr()
    {
        return 60 * 60;
    }

    /**
     * {@inheritdoc}
     */
    public function canRetry($attempt, $error)
    {
        $this->addErrorLog($attempt, $error);

        return ($attempt < 3);
    }
}
