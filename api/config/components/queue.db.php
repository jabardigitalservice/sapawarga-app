<?php

return [
    'class' => \yii\queue\db\Queue::class,
    'db' => 'db',
    'tableName' => '{{%queue}}',
    'channel' => 'default',
    'mutex' => \yii\mutex\MysqlMutex::class,
    'serializer' => \yii\queue\serializers\JsonSerializer::class,
    'as log' => \yii\queue\LogBehavior::class,
];
