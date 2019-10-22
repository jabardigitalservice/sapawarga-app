<?php

use Jdsteam\Yii2Sentry\SentryTarget;
use yii\log\FileTarget;

return [
    'traceLevel' => YII_DEBUG ? 3 : 0,
    'targets' => [
        [
            'class' => FileTarget::class,
            'levels' => ['error', 'warning', 'info'],
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
