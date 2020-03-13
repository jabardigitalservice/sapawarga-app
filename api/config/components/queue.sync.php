<?php

use Jdsteam\Sapawarga\Jobs\ImportUserJob;
use yii\queue\ExecEvent;

return [
    'class' => \yii\queue\sync\Queue::class,
    'handle' => true,
    'as log' => \yii\queue\LogBehavior::class,
];
