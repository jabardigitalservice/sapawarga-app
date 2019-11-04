<?php

use Jdsteam\Sapawarga\Jobs\ImportUserJob;
use yii\queue\ExecEvent;

return [
    'class' => \yii\queue\sync\Queue::class,
    'handle' => true,
    'as log' => \yii\queue\LogBehavior::class,
    'on afterError' => function (ExecEvent $event) {
        if ($event->job instanceof ImportUserJob) {
            $event->job->notifyError($event->error);
        }
    },
];
