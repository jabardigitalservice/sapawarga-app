<?php

namespace app\modules\v1\controllers;

use app\models\Area;
use creocoder\flysystem\Filesystem;
use Illuminate\Support\Collection;
use Jdsteam\Sapawarga\Models\Contracts\ActiveStatus;
use Yii;
use yii\base\DynamicModel;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * BansosUploadController implements the CRUD actions for Banner model.
 */
class BansosUploadController extends ActiveController implements ActiveStatus
{
    public const STATUS_INVALID = 20;

    public $modelClass = DynamicModel::class;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['verbs'] = [
            'class'   => VerbFilter::class,
            'actions' => [
                'index'  => ['get'],
                'upload' => ['post'],
            ],
        ];

        return $this->behaviorCors($behaviors);
    }

    protected function behaviorAccess($behaviors)
    {
        // setup access
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'only'  => ['index', 'upload'],
            'rules' => [
                [
                    'allow'   => true,
                    'actions' => ['index', 'upload'],
                    'roles'   => ['admin', 'staffKabkota'],
                ],
            ],
        ];

        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();

        // Override Actions
        unset($actions['index']);
        unset($actions['create']);
        unset($actions['view']);
        unset($actions['update']);
        unset($actions['delete']);

        return $actions;
    }

    public function actionIndex()
    {
        $user = Yii::$app->user;

        $rows = (new Query())
            ->from('bansos_bnba_upload_histories')
            ->join('join', 'areas b', 'bansos_bnba_upload_histories.kabkota_code = b.code_bps')
            ->select('bansos_bnba_upload_histories.*, b.name as kabkota_name')
            ->where(['bansos_bnba_upload_histories.user_id' => $user->id])
            ->orderBy(['bansos_bnba_upload_histories.created_at' => SORT_DESC])
            ->all();

        $rows = new Collection($rows);

        return $rows->map(function ($row) {
            return [
                'id'                => (int) $row['id'],
                'bansos_type'       => (int) $row['bansos_type'],
                'kabkota_code'      => $row['kabkota_code'],
                'kabkota_name'      => $row['kabkota_name'],
                'kec_code'          => $row['kec_code'],
                'notes'             => $row['notes'],
                'original_filename' => $row['original_filename'],
                'file_name'         => $this->getFileName($row['file_path']),
                'file_url'          => $this->getFileUrl($row['file_path']),
                'invalid_file_name' => $this->getFileName($row['invalid_file_path']),
                'invalid_file_url'  => $this->getFileUrl($row['invalid_file_path']),
                'status'            => $row['status'],
                'created_at'        => (int) $row['created_at'],
            ];
        });
    }

    public function actionUpload()
    {
        /**
         * @var Filesystem $filesystem
         */
        $filesystem = Yii::$app->fs;
        $user       = Yii::$app->user;
        $type       = Yii::$app->request->post('type');
        $kabkotaId  = Yii::$app->request->post('kabkota_id');
        $kecId      = Yii::$app->request->post('kec_id');

        $file = UploadedFile::getInstanceByName('file');

        $model = new DynamicModel(['file' => $file, 'type' => $type, 'kabkota_id' => $kabkotaId, 'kec_id' => $kecId]);

        $model->addRule('file', 'required');
        $model->addRule('file', 'file', ['extensions' => 'xlsx, xls', 'checkExtensionByMimeType' => false]);

        $model->addRule('type', 'trim');
        $model->addRule('type', 'required');
        $model->addRule('kabkota_id', 'trim');
        $model->addRule('kabkota_id', 'required');

        if ($model->validate() === false) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(422);

            return $model->getErrors();
        }

        $kabkota   = Area::findOne(['id' => $kabkotaId]);
        $code      = $kabkota->code_bps;
        $kecamatan = null;

        if ($kecId !== null) {
            $kecamatan = Area::findOne(['id' => $kecId]);
            $code      = $kecamatan->code_bps;
        }

        $ext          = $file->getExtension();
        $date         = date('Ymd_His');
        $relativePath = "bansos-bnba/{$code}_{$type}_{$date}.{$ext}";

        $filesystem->write($relativePath, file_get_contents($file->tempName));

        // trigger process-excel API
        $url = Yii::$app->params['bansosProcessExcelUrl'] . '/process-excel/';

        $client = new Client([
            'timeout'  => 0.00000000000001,
        ]);

        try {
            $response = $client->post($url, [
                'json' => [
                    'bucket_name' => $filesystem->bucket,
                    'path_file_s3' => $relativePath,
                    'file_name' => explode('/', $relativePath)[1],
                    's3_records' => 'dummy',
                ],
            ]);
        } catch (RequestException $e) {
        }

        // get current tahapan
        $current_tahap = (new \yii\db\Query())
        ->from('beneficiaries_current_tahap')
        ->all();

        if (count($current_tahap) <= 0) {
            throw new \yii\base\InvalidValueException();
        }

        $record = [
            'user_id'           => $user->id,
            'bansos_type'       => $type,
            'kabkota_code'      => $kabkota->code_bps,
            'kec_code'          => $kecamatan ? $kecamatan->code_bps : null,
            'original_filename' => $file->name,
            'file_path'         => $relativePath,
            'status'            => 0,
            'created_at'        => time(),
            'updated_at'        => time(),
            'created_by'        => $user->id,
            'updated_by'        => $user->id,
            'tahap_bantuan'     => $current_tahap[0]['current_tahap_bnba'],
        ];

        Yii::$app->db->createCommand()->insert('bansos_bnba_upload_histories', $record)->execute();

        return ['file_path' => $this->getFileUrl($relativePath)];
    }

    /**
     * @return string
     */
    public function getFileUrl($relativePath)
    {
        if (!$relativePath) {
            return null;
        }

        $publicBaseUrl = Yii::$app->params['storagePublicBaseUrl'];

        return "{$publicBaseUrl}/{$relativePath}";
    }

    /**
     * @return string
     */
    public function getFileName($relativePath)
    {
        if (!$relativePath) {
            return null;
        }
        return basename($relativePath);
    }
}
