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

    public $userId;

    public function execute($queue)
    {
        \Yii::$app->language = 'id-ID';

        $this->jobHistoryClassName = 'app\models\BansosBnbaDownloadHistory';
        $jobHistory = $this->jobHistory;
        $jobHistory->setStart();

        // size of query batch size used during database retrieval
        $batchSize = 1000;
        echo 'Params: ';
        print_r($jobHistory->params);

        $query = $jobHistory->getQuery();

        $rowNumbers = $jobHistory->total_row;
        echo "Number of rows to be processed : $rowNumbers" . PHP_EOL;

        echo "Starting generating BNBA list export\n" ;

        /* Generate export file using box/spout library.
         * ref: https://opensource.box.com/spout/getting-started/#writer */
        $writer = WriterEntityFactory::createXLSXWriter();

        // Initial varieble location, filename, path
        $nowDate = date('Y-m-d-H-i-s');
        $fileName = "export-bnba-$nowDate.xlsx";
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
        $columnHeaders = array_merge($columns, ['Pintu Bantuan']);
        $columnValues = array_merge($columns, ['bansostype']);

        /** Shortcut: add a row from an array of values */
        $rowFromValues = WriterEntityFactory::createRowFromArray($columnHeaders);
        $writer->addRow($rowFromValues);

        // create unbuffered database connection to avoid MySQL batching limitation
        // ref: https://www.yiiframework.com/doc/guide/2.0/en/db-query-builder#batch-query-mysql
        $unbufferedDb = new \yii\db\Connection([
            'dsn' => Yii::$app->db->dsn,
            'username' => Yii::$app->db->username,
            'password' => Yii::$app->db->password,
            'charset' => Yii::$app->db->charset,
        ]);
        $unbufferedDb->open();
        $unbufferedDb->pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);

        $numProcessed = 0;
        foreach ($query->batch($batchSize, $unbufferedDb) as $listBnba) {
            $data = ArrayHelper::toArray($listBnba, [
                'app\models\BeneficiaryBnbaTahapSatu' => $columnValues,
            ]);

            foreach ($data as $row) {
                $rowFromValues = WriterEntityFactory::createRowFromArray($row);
                $writer->addRow($rowFromValues);
            }

            $numProcessed += count($data);
            echo sprintf("Processed : %d/%d (%.2f%%)\n", $numProcessed, $rowNumbers, ($numProcessed * 100 / $rowNumbers));

            $jobHistory->processed_row = $numProcessed;
            $jobHistory->save();
        }

        $writer->close();
        $unbufferedDb->close();

        $jobHistory->setFinish();

        echo 'Finished generating export file' . PHP_EOL;

        // upload to S3 & send notification email
        $relativePath = "export-bnba-list/$fileName";
        $uploadJob = new UploadS3Job([
            'jobHistoryClassName' => 'app\models\BansosBnbaDownloadHistory',
            'relativePath' => $relativePath,
            'filePathTemp' => $filePathTemp,
            'userId' => $this->userId,
            'historyId' => $this->historyId,
            'emailNotifParam' => [
                'template' => ['html' => 'email-result-export-list-bnba'],
                'subject' => 'Notifikasi dari Sapawarga: Hasil export daftar BNBA sudah bisa diunduh!',
            ],
        ]);
        $uploadJob->execute(Yii::$app->queue);
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
