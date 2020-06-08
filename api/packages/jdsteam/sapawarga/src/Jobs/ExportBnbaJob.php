<?php

namespace Jdsteam\Sapawarga\Jobs;

use Yii;
use yii\base\BaseObject;
use yii\queue\JobInterface;
use app\models\BeneficiaryBnbaTahapSatu;
use app\models\User;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;
use League\Flysystem\AdapterInterface;

class ExportBnbaJob extends BaseObject implements JobInterface
{
    public $params;
    public $user_id;

    public function execute($queue)
    {
        // size of query batch size used during database retrieval
        $batch_size = 1000;

        $query = BeneficiaryBnbaTahapSatu::find()->where($this->params);
        //$list_bnba = $query->all();

        $row_numbers = $query->count();
        echo "Number of rows to be processed : $row_numbers" . PHP_EOL;

        echo sprintf( "Starting generating BNBA list export for kode_kab %s", $this->params['kode_kab']);
        if ( isset($this->params['kode_kec']) && is_array($this->params['kode_kec']) )
          echo sprintf( " and kode_kec %s", implode(',', $this->params['kode_kec'] ));
        echo PHP_EOL;

        /* Generate export file using box/spout library.
         * ref: https://opensource.box.com/spout/getting-started/#writer */
        $writer = WriterEntityFactory::createXLSXWriter();
        // $writer = WriterEntityFactory::createODSWriter();
        //$writer = WriterEntityFactory::createCSVWriter();
        
        // Initial varieble location, filename, path
        $publicBaseUrl = Yii::$app->params['storagePublicBaseUrl'];
        $nowDate = date('Y-m-d-H-i-s');
        $fileName = "export-bnba-tahap-1-$nowDate.xlsx";
        $filePathTemp = Yii::getAlias('@app/web') . '/storage/' . $fileName;

        $writer->openToFile($filePathTemp); // write data to a file or to a PHP stream
        //\Yii::$app->response->format = \yii\web\Response::FORMAT_RAW; // return raw response
        //\Yii::$app->response->setStatusCode(200)->send();
        //$writer->openToBrowser($fileName); // stream data directly to the browser

        $columns = [
            'id',
            //'is_nik_valid' ,
            //'is_dtks',
            //'id_tipe_bansos',
            //'id_tipe_bansos_name',
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
            //'lapangan_usaha_type',
            'status_kedudukan',
            'penghasilan_sebelum_covid19',
            'penghasilan_setelah_covid',
            'keterangan',
        ];
        /** Shortcut: add a row from an array of values */
        $rowFromValues = WriterEntityFactory::createRowFromArray($columns);
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
        $relativePath = "export-bnba-list/$fileName";

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
        Yii::$app->queue->ttr(30 * 60)->push(new GenericEmailJob([
            'destination' => $user->email,
            'template' => ['html' => 'email-result-export-list-bnba'],
            'content' => [
                'final_url' => $final_url,
            ],
            'subject' => 'Notifikasi dari Sapawarga : Hasil export list BNBA',
        ]));

    }
}
