<?php

namespace Jdsteam\Sapawarga\Jobs;

use yii\base\BaseObject;
use yii\queue\JobInterface;

class DownloadJob extends BaseObject implements JobInterface
{
    public $url;
    public $file;

    public function execute($queue)
    {
        echo 'executed!';
    }
}