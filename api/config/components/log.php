<?php

use Jdsteam\Yii2StreamLog\StreamLogTarget;
use Jdsteam\Yii2Sentry\SentryTarget;
use yii\log\FileTarget;

return [
    'traceLevel' => 0,
    'targets' => [
        [
            'class' => FileTarget::class,
            'levels' => ['error', 'warning', 'info'],
        ],
        [
            'class' => StreamLogTarget::class,
            'url' => 'php://stdout',
            'levels' => ['info','trace'],
            'logVars' => [],
        ],
        [
            'class' => StreamLogTarget::class,
            'url' => 'php://stderr',
            'levels' => ['error', 'warning'],
            'logVars' => [],
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
