<?php

namespace app\modules\v1\controllers;

use app\models\Area;
use creocoder\flysystem\Filesystem;
use Yii;
use yii\base\DynamicModel;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * BansosUploadController implements the CRUD actions for Banner model.
 */
class BansosUploadController extends ActiveController
{
    public $modelClass = DynamicModel::class;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['verbs'] = [
            'class'   => VerbFilter::class,
            'actions' => [
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
            'only'  => ['upload'],
            'rules' => [
                [
                    'allow'   => true,
                    'actions' => ['upload'],
                    'roles'   => ['admin', 'staffKabkota'],
                ],
            ],
        ];

        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();

        return $actions;
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

        $record = [
            'user_id'      => $user->id,
            'bansos_type'  => $type,
            'kabkota_code' => $kabkota->code_bps,
            'kec_code'     => $kecamatan ? $kecamatan->code_bps : null,
            'file_path'    => $relativePath,
            'status'       => 0,
            'created_at'   => time(),
            'updated_at'   => time(),
            'created_by'   => $user->id,
            'updated_by'   => $user->id,
        ];

        Yii::$app->db->createCommand()->insert('bansos_bnba_upload_histories', $record)->execute();

        return ['file_path' => $this->getFileUrl($relativePath)];
    }

    /**
     * @return string
     */
    public function getFileUrl($relativePath)
    {
        $publicBaseUrl = Yii::$app->params['storagePublicBaseUrl'];

        return "{$publicBaseUrl}/{$relativePath}";
    }
}
