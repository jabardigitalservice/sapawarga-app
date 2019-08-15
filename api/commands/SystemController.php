<?php

namespace app\commands;

use Jdsteam\Sapawarga\Jobs\DownloadJob;
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
}
