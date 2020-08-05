<?php

namespace Jdsteam\Sapawarga\Jobs;

use Yii;
use yii\base\BaseObject;
use yii\queue\RetryableJobInterface;
use yii\db\Query;
use app\models\Beneficiary;
use app\models\User;
use yii\helpers\ArrayHelper;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use League\Flysystem\AdapterInterface;
use Jdsteam\Sapawarga\Jobs\Concerns\HasJobHistory;

class ExportBeneficiariesJob extends BaseObject implements RetryableJobInterface
{
    use HasJobHistory;

    public $userId;

    public function execute($queue)
    {
        $this->jobHistoryClassName = 'app\models\BansosBeneficiariesDownloadHistory';
        $jobHistory = $this->jobHistory;
        $jobHistory->setStart();

        // size of query batch size used during database retrieval
        $batch_size = 1000;

        echo "Params:". PHP_EOL;
        print_r($jobHistory->params);

        $columnHeaders = array_keys($jobHistory->columns);
        $columnHeaders[count($columnHeaders)-1] = 'Status Verifikasi';

        Yii::$app->language = 'id-ID';
        function getStatusLabel($status) {
            $localizationKey = Beneficiary::STATUS_VERIFICATION_LABEL[$status];
            return Yii::t('app', $localizationKey);
        }

        $query = $jobHistory->getQuery();
        $row_numbers = $jobHistory->total_row;
        echo "Number of rows to be processed : $row_numbers" . PHP_EOL;

        echo "Starting generating BNBA list export\n" ;

        /* Generate export file using box/spout library.
         * ref: https://opensource.box.com/spout/getting-started/#writer */
        $writer = WriterEntityFactory::createXLSXWriter();

        // Initial varieble location, filename, path
        $now_date = date('Y-m-d-H-i-s');
        $fileName = "export-calon-penerima-bantuan-$now_date.xlsx";
        $filePathTemp = Yii::getAlias('@app/web') . '/storage/' . $fileName;

        $writer->openToFile($filePathTemp); // write data to a file or to a PHP stream
        /** Shortcut: add a row from an array of values */
        $rowFromValues = WriterEntityFactory::createRowFromArray($columnHeaders);
        $writer->addRow($rowFromValues);

        // create unbuffered database connection to avoid MySQL batching limitation
        // ref: https://www.yiiframework.com/doc/guide/2.0/en/db-query-builder#batch-query-mysql
        $unbefferedDb = new \yii\db\Connection([
            'dsn' => Yii::$app->db->dsn,
            'username' => Yii::$app->db->username,
            'password' => Yii::$app->db->password,
            'charset' => Yii::$app->db->charset,
        ]);
        $unbefferedDb->open();
        $unbefferedDb->pdo->setAttribute(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, false);

        $num_processed = 0;
        foreach ($query->batch($batch_size, $unbefferedDb) as $listBnba)
        {
            $listBnba = array_map(function ($item) {
                $item['keterangan'] = null;
                $item['status_verifikasi'] = getStatusLabel($item['status_verifikasi']);
                return $item;
            }, $listBnba);

            foreach ($listBnba as $row) {
                $rowFromValues = WriterEntityFactory::createRowFromArray($row);
                $writer->addRow($rowFromValues);
            }

            $num_processed += count($listBnba);
            echo sprintf("Processed : %d/%d (%.2f%%)\n", $num_processed, $row_numbers, ($num_processed*100/$row_numbers));

            $jobHistory->processed_row = $num_processed;
            $jobHistory->save();
        }

        $writer->close();
        $unbefferedDb->close();

        $jobHistory->setFinish();

        echo "Finished generating export file" . PHP_EOL;

        // upload to S3 & send notification email
        $relativePath = "export-beneficiaries-list/$fileName";
        Yii::$app->queue->priority(10)->push(new UploadS3Job([
            'jobHistoryClassName' => $this->jobHistoryClassName,
            'relativePath' => $relativePath,
            'filePathTemp' => $filePathTemp,
            'userId' => $this->userId,
            'historyId' => $this->historyId,
            'emailNotifParam' => [
                'template' => ['html' => 'email-result-export-list-beneficiaries'],
                'subject' => 'Notifikasi dari Sapawarga: Hasil export Daftar Calon Penerima Bantuan sudah bisa diunduh!',
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
