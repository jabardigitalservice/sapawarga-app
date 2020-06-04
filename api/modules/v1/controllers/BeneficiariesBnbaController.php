<?php

namespace app\modules\v1\controllers;

use app\models\Area;
use app\models\BeneficiaryBnbaTahapSatu;
use app\models\BeneficiaryBnbaTahapSatuSearch;
use app\validator\NikRateLimitValidator;
use app\validator\NikValidator;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Yii;
use yii\base\DynamicModel;
use yii\filters\AccessControl;
use yii\web\HttpException;
use yii\helpers\ArrayHelper;
use Illuminate\Support\Collection;
use Illuminate\Support\Arr;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Common\Entity\Row;

/**
 * BeneficiariesBnbaTahapSatuController implements the CRUD actions for BeneficiaryBnbaTahapSatu model.
 */
class BeneficiariesBnbaController extends ActiveController
{
    public $modelClass = BeneficiaryBnbaTahapSatu::class;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        return $this->behaviorCors($behaviors);
    }

    protected function behaviorAccess($behaviors)
    {
        // setup access
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => ['download'],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['download'],
                    'roles' => ['admin', 'staffProv', 'staffKabkota', 'staffKec', 'staffKel', 'staffRW', 'trainer'],
                ]
            ],
        ];

        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();

        // Override Actions
        unset($actions['create']);
        unset($actions['view']);
        unset($actions['update']);
        unset($actions['delete']);

        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

        return $actions;
    }

    public function actionDownload()
    {
        $params = Yii::$app->request->getQueryParams();

        $user = Yii::$app->user;
        $authUserModel = $user->identity;

        if ($user->can('staffKabkota')) {
            $parent_area = Area::find()->where(['id' => $authUserModel->kabkota_id])->one();
            $params['kode_kab'] = $parent_area->code_bps;
            if (isset($params['kode_kec'])) {
                $params['kode_kec'] = explode(',', $params['kode_kec']);
            }
        } elseif ($user->can('staffProv')) {
            if (isset($params['kode_kec'])) {
                $params['kode_kec'] = explode(',', $params['kode_kec']);
            }
        }

        $query = BeneficiaryBnbaTahapSatu::find()->where($params);
        $list_bnba = $query->all();

        /* Generate export file using box/spout library.
         * ref: https://opensource.box.com/spout/getting-started/#writer */
        $writer = WriterEntityFactory::createXLSXWriter();
        // $writer = WriterEntityFactory::createODSWriter();
        //$writer = WriterEntityFactory::createCSVWriter();
        
        // Initial varieble location, filename, path
        $publicBaseUrl = Yii::$app->params['storagePublicBaseUrl'];
        $nowDate = date('Y-m-d-H-i-s');
        $fileName = "export-bnba-tahap-1-$nowDate.xlsx";
        $filenameTemp = 'temp-' . $fileName;
        $filePathTemp = Yii::getAlias('@webroot/storage') . '/' . $filenameTemp;

        $writer->openToFile($filePathTemp); // write data to a file or to a PHP stream
        //$writer->openToBrowser($fileName); // stream data directly to the browser

        /** Shortcut: add a row from an array of values */
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
        $rowFromValues = WriterEntityFactory::createRowFromArray($columns);
        $writer->addRow($rowFromValues);
        $data = ArrayHelper::toArray($list_bnba, [
            'app\models\BeneficiaryBnbaTahapSatu' => $columns,
        ]);

        foreach ($data as $row) {
            $rowFromValues = WriterEntityFactory::createRowFromArray($row);
            $writer->addRow($rowFromValues);
        }

        $writer->close();

        $response = \Yii::$app->response->sendFile($filePathTemp, $fileName, ['mimeType'=>'applications/xlsx']);
        unlink($filePathTemp);

        return $response;
    }
}
