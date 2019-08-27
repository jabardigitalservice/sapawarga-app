<?php

return [
    'class' => \yii\queue\db\Queue::class,
    'db' => 'db',
    'tableName' => '{{%queue}}',
    'channel' => 'default',
    'mutex' => \yii\mutex\MysqlMutex::class,
    'as broadcast' => Jdsteam\Sapawarga\Behaviors\BroadcastBehavior::class,
];
