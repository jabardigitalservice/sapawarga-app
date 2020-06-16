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

class ExportBeneficiariesJob extends BaseObject implements RetryableJobInterface
{
    public $params;
    public $user_id;

    public function execute($queue)
    {
        // size of query batch size used during database retrieval
        $batch_size = 1000;

        echo "Starting generating BNBA list export\n" ;
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
          ->andWhere(['not', ['domicile_rw' => [0,null] ] ])
          ;

        $row_numbers = $query->count();
        echo "Number of rows to be processed : $row_numbers" . PHP_EOL;

        /* Generate export file using box/spout library.
         * ref: https://opensource.box.com/spout/getting-started/#writer */
        $writer = WriterEntityFactory::createXLSXWriter();
        
        // Initial varieble location, filename, path
        $publicBaseUrl = Yii::$app->params['storagePublicBaseUrl'];
        $nowDate = date('Y-m-d-H-i-s');
        $fileName = "export-calon-penerima-bantuan-$nowDate.xlsx";
        $filePathTemp = Yii::getAlias('@app/web') . '/storage/' . $fileName;

        $writer->openToFile($filePathTemp); // write data to a file or to a PHP stream
        /** Shortcut: add a row from an array of values */
        $rowFromValues = WriterEntityFactory::createRowFromArray(array_keys($columns));
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

        $num_processed = 0;
        foreach ($query->batch($batch_size, $unbufferedDb) as $list_bnba)
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
        }

        $writer->close();
        $unbufferedDb->close();

        echo "Finished generating export file" . PHP_EOL;

        // upload to S3
        echo "Uploading to S3 storage" . PHP_EOL;
        $filesystem = Yii::$app->fs;
        $relativePath = "export-beneficiaries-list/$fileName";

        $stream = fopen($filePathTemp, 'r+');
        $publicBaseUrl = Yii::$app->params['storagePublicBaseUrl'];

        $filesystem->writeStream($relativePath, $stream);
        // if S3 account does not provide cloudfront for publicly accessing file, 
        // we must manually set public ACL. in that case, use below code instead of above
        //$filesystem->writeStream($relativePath, $stream, [
            //'visibility' => AdapterInterface::VISIBILITY_PUBLIC
        //]);

        $final_url = $publicBaseUrl;
        // if S3 account does not provide cloudfront for publicly accessing file
        // we could use generic amazon s3 url (only if file already has public access ACL)
        // in that case, use below code instead of above
        //$final_url = sprintf('https://%s.s3.%s.amazonaws.com', $filesystem->bucket, $filesystem->region);
        $final_url .= "/$relativePath";
        unlink($filePathTemp);

        echo "Upload finished. Final url: $final_url" . PHP_EOL;

        // send result notification to user
        echo "Sending notification email" . PHP_EOL;
        $user = User::findOne($this->user_id);
        Yii::$app->queue->priority(10)->push(new GenericEmailJob([
            'destination' => $user->email,
            'template' => ['html' => 'email-result-export-list-beneficiaries'],
            'content' => [
                'final_url' => $final_url,
            ],
            'subject' => 'Notifikasi dari Sapawarga: Hasil export Daftar Calon Penerima Bantuan sudah bisa diunduh!',
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
