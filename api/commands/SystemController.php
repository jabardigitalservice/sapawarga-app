<?php

namespace app\commands;

use Jdsteam\Sapawarga\Jobs\DownloadJob;
use Jdsteam\Sapawarga\Jobs\EmailJob;
use Jdsteam\Sapawarga\Jobs\ImportUserJob;
use Yii;
use yii\console\Controller;

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
            'file' => __DIR__ . '/../web/export.csv',
        ]));
    }
}
