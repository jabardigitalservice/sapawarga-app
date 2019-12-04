<?php

use Mero\Monolog\MonologComponent;

return [
    'class' => MonologComponent::class,
    'channels' => [
        'main' => [
            'handler' => [
                [
                    'type' => 'stream',
                    'path' => '@app/runtime/logs/main.log',
                    'level' => 'debug'
                ]
            ],
            'processor' => [],
        ],
        'import-users' => [
            'handler' => [
                [
                    'type' => 'error_log',
                ],
                [
                    'type' => 'stream',
                    'path' => '@app/runtime/logs/import_users.log',
                    'level' => 'debug'
                ]
            ],
            'processor' => [],
        ],
    ],
];
