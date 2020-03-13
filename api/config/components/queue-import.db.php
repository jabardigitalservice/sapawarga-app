<?php

use Jdsteam\Sapawarga\Jobs\ImportUserJob;
use yii\queue\ExecEvent;

return [
    'class' => \yii\queue\db\Queue::class,
    'db' => 'db',
    'tableName' => '{{%queue}}',
    'channel' => 'import',
    'mutex' => \yii\mutex\MysqlMutex::class,
    'serializer' => \yii\queue\serializers\JsonSerializer::class,
    'as log' => \yii\queue\LogBehavior::class,
    'on afterError' => function (ExecEvent $event) {
        $event->job->notifyError($event->error);
    },
];
