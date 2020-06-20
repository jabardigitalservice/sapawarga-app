<?php

namespace app\modules\v1\controllers;

use app\models\Area;
use creocoder\flysystem\Filesystem;
use app\models\BansosVervalUploadHistory;
use app\models\BansosVervalUploadHistorySearch;
use Yii;
use yii\base\DynamicModel;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\web\HttpException;
use Illuminate\Support\Arr;
use yii\web\UploadedFile;

/**
 * BeneficiariesVervalUpload implements manual upload for verval.
 */
class BeneficiariesVervalUploadController extends ActiveController
{
    public $modelClass = BansosVervalUploadHistory::class;

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
            'only' => ['index', 'view', 'upload'],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['index', 'view', 'upload'],
                    'roles' => ['admin', 'staffProv', 'staffKabkota', 'staffKec', 'staffKel'],
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
        unset($actions['index']);

        return $actions;
    }

    public function actionIndex()
    {
        $params = Yii::$app->request->getQueryParams();

        $user = Yii::$app->user;
        $params['user_id'] = $user->id;

        $search = new BansosVervalUploadHistorySearch();

        return $search->search($params);
    }

    public function actionUpload()
    {
        /**
         * @var Filesystem $filesystem
         */
        $filesystem = Yii::$app->fs;

        $user = Yii::$app->user;
        $userIdentity = $user->identity;

        $vervalType = null;
        if ($userIdentity->role == 80) {
            $vervalType = 'kabkota';
        } elseif ($userIdentity->role == 70) {
            $vervalType = 'kecamatan';
        } elseif ($userIdentity->role == 60) {
            $vervalType = 'kelurahan';
        }

        $kabkotaId  = $userIdentity->kabkota_id;
        $kecId = $userIdentity->kec_id;
        $kelId = $userIdentity->kel_id;

        $file = UploadedFile::getInstanceByName('file');

        $model = new DynamicModel(['file' => $file, 'verval_type' => $vervalType, 'kabkota_id' => $kabkotaId, 'kec_id' => $kecId, 'kel_id' => $kelId]);

        $model->addRule('file', 'required');
        $model->addRule('file', 'file', ['extensions' => 'xlsx, xls', 'checkExtensionByMimeType' => false]);

        $model->addRule('verval_type', 'trim');
        $model->addRule('verval_type', 'required');
        $model->addRule('kabkota_id', 'trim');
        $model->addRule('kabkota_id', 'required');

        if ($model->validate() === false) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(422);

            return $model->getErrors();
        }

        $kabkota = Area::findOne(['id' => $kabkotaId]);
        $code = $kabkota->code_bps;
        $kec = null;

        if ($kecId !== null) {
            $kec = Area::findOne(['id' => $kecId]);
            $code = $kec->code_bps;
        }

        if ($kelId !== null) {
            $kel = Area::findOne(['id' => $kelId]);
            $code = $kel->code_bps;
        }

        $ext = $file->getExtension();
        $date = date('Ymd_His');
        $relativePath = "bansos-verval/{$code}_{$vervalType}_{$date}.{$ext}";

        $filesystem->write($relativePath, file_get_contents($file->tempName));

        $record = [
            'user_id'           => $user->id,
            'verval_type'       => $vervalType,
            'kabkota_code'      => $kabkota->code_bps,
            'kec_code'          => $kecId ? $kec->code_bps : null,
            'kel_code'          => $kelId ? $kel->code_bps : null,
            'original_filename' => $file->name,
            'file_path'         => $relativePath,
            'status'            => 0,
            'created_at'        => time(),
            'updated_at'        => time(),
            'created_by'        => $user->id,
            'updated_by'        => $user->id,
        ];

        Yii::$app->db->createCommand()->insert('bansos_verval_upload_histories', $record)->execute();

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
