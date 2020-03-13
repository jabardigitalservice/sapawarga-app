<?php

namespace app\commands;

use Jdsteam\Sapawarga\Jobs\DownloadJob;
use Jdsteam\Sapawarga\Jobs\EmailJob;
use Jdsteam\Sapawarga\Jobs\ImportUserJob;
use Yii;
use yii\console\Controller;
use yii\web\BadRequestHttpException;

class SystemController extends Controller
{
    public function actionTestDispatchJob()
    {
        Yii::$app->queue->push(new DownloadJob([
            'url' => 'http://example.com/image.jpg',
            'file' => '/tmp/image.jpg',
        ]));
    }

    public function actionTestEmailJob()
    {
        Yii::$app->queue->push(new EmailJob());
    }

    public function actionTestImportUserJob()
    {
        Yii::$app->queue->push(new ImportUserJob([
            'uploaderEmail' => 'yohang88@gmail.com',
            'filePath' => __DIR__ . '/../web/export.csv',
        ]));
    }

    public function actionTestSentry()
    {
        throw new BadRequestHttpException('Test send sentry error.');
    }
}
