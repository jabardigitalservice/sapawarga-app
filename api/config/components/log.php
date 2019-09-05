<?php

use Jdsteam\Yii2Sentry\SentryTarget;

return [
    'traceLevel' => YII_DEBUG ? 3 : 0,
    'targets' => [
        [
            'class' => 'yii\log\FileTarget',
            'levels' => ['error', 'warning'],
        ],
        [
            'class' => SentryTarget::class,
            'enabled' => env('ERROR_REPORT', false),
            'environment' => env('ERROR_ENVIRONMENT', 'staging'),
            'dsn' => env('SENTRY_DSN'),
            'levels' => ['error', 'warning'],
            'context' => true
        ],
    ],
];
