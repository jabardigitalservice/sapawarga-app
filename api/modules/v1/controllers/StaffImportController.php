<?php

namespace app\modules\v1\controllers;

use app\filters\auth\HttpBearerAuth;
use app\models\User;
use app\models\UserImport;
use app\models\UserImportUploadForm;
use Jdsteam\Sapawarga\Jobs\ImportUserJob;
use Yii;
use yii\filters\AccessControl;
use yii\filters\auth\CompositeAuth;
use yii\filters\Cors;
use yii\filters\VerbFilter;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\UploadedFile;

class StaffImportController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => CompositeAuth::class,
            'authMethods' => [
                HttpBearerAuth::class,
            ],
        ];

        $behaviors['verbs'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'download-template' => ['get'],
                'import' => ['post'],
            ],
        ];

        $auth = $behaviors['authenticator'];
        unset($behaviors['authenticator']);

        $behaviors['corsFilter'] = [
            'class' => Cors::class,
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
            ],
        ];

        $behaviors['authenticator'] = $auth;
        $behaviors['authenticator']['except'] = ['options'];

        $behaviors['access'] = [
            'class' => AccessControl::class,
            'only' => ['download-template', 'import'],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['download-template', 'import'],
                    'roles' => ['admin', 'manageStaffs'],
                ],
            ],
        ];

        return $behaviors;
    }

    /**
     * Get template file URL for download
     * GET /staff/import-template
     *
     * @return array
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionDownloadTemplate(): array
    {
        $filePath = UserImport::generateTemplateFile();

        if (file_exists($filePath) === false) {
            throw new NotFoundHttpException("File not found: $filePath");
        }

        $fileUrl = $this->copyLocalToStorage($filePath);

        return ['file_url' => $fileUrl];
    }

    /**
     * Copy template local file to Storage Object, then get URL from Storage
     *
     * @param $sourcePath
     * @return string
     */
    protected function copyLocalToStorage($sourcePath): string
    {
        $destinationPath = 'template-users-import.xlsx';

        $contents = file_get_contents($sourcePath);

        Yii::$app->fs->put($destinationPath, $contents);

        $fileUrl = sprintf('%s/%s', Yii::$app->params['storagePublicBaseUrl'], $destinationPath);

        return $fileUrl;
    }

    /**
     * Validate uploaded file, upload to Storage
     * Then create new Queue Job for later processing
     * POST /staff/import
     *
     * @return array
     * @throws \yii\web\ServerErrorHttpException
     */
    public function actionImport(): array
    {
        $currentUser = User::findIdentity(Yii::$app->user->getId());

        $model       = new UserImportUploadForm();
        $model->file = UploadedFile::getInstanceByName('file');

        if ($model->validate() === false) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(422);

            return $model->getErrors();
        }

        // Upload to S3 and push new queue job for async/later processing
        if ($filePath = $model->upload()) {
            $this->pushQueueJob($currentUser, $filePath);

            return ['file_path' => $filePath];
        }

        throw new ServerErrorHttpException('Failed to upload the object for unknown reason.');
    }

    /**
     * Push new ImportUserJob to queue
     *
     * @param $user
     * @param $filePath
     */
    protected function pushQueueJob($user, $filePath): void
    {
        Yii::$app->queue->push(new ImportUserJob([
            'filePath'      => $filePath,
            'uploaderEmail' => $user->email,
        ]));
    }
}
