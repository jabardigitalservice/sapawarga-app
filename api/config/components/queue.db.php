<?php

use Jdsteam\Sapawarga\Jobs\ImportUserJob;
use yii\queue\ExecEvent;

return [
    'class' => \yii\queue\db\Queue::class,
    'db' => 'db',
    'tableName' => '{{%queue}}',
    'channel' => 'default',
    'mutex' => \yii\mutex\MysqlMutex::class,
    'serializer' => \yii\queue\serializers\JsonSerializer::class,
    'as log' => \yii\queue\LogBehavior::class,
    'deleteReleased' => false,
];
