<?php

$params = include __DIR__ . '/params.php';

$config = [
    'id' => 'boilerplate-api',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'queue', 'queueImport'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'sourceLanguage' => 'en-US',
    'language' => 'id-ID',
    'components' => [
        'request' => [
            'cookieValidationKey' => getenv('COOKIE_VALIDATION_KEY'),
            'parsers' => [
                'application/json' => 'yii\web\JsonParser',
            ],
        ],
        'cache' => [
            'class' => 'yii\caching\MemCache',
            'useMemcached' => getenv('CACHE_USE_MEMCACHED'),
            'username' => getenv('CACHE_USERNAME'),
            'password' => getenv('CACHE_PASSWORD'),
            'servers' => [
                [
                    'host' => getenv('CACHE_SERVERS'),
                    'port' => getenv('CACHE_PORT'),
                    'weight' => getenv('CACHE_WEIGHT'),
                ],
            ],
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],
        'assetManager' => [
            'baseUrl' => '/assets',
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => include __DIR__ . '/components/mailer.php',
        'log' => include __DIR__ . '/components/log.php',
        'monolog' => include __DIR__ . '/components/monolog.php',
        'queue' => include __DIR__ . '/components/queue.db.php',
        'queueImport' => include __DIR__ . '/components/queue-import.db.php',
        'db' => include __DIR__ . '/db.php',

        'urlManager' => [
            'baseUrl' => '/',    // Added for
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => true,
            'rules' => include __DIR__ . '/routes.php',
        ],
        'response' => [
            'class' => 'yii\web\Response',
            'on beforeSend' => function ($event) {
                $response = $event->sender;
                if (in_array($response->format, ['html', 'raw'])) {
                    return $response;
                }

                $responseData = $response->data;

                if (is_string($responseData) && json_decode($responseData)) {
                    $responseData = json_decode($responseData, true);
                }

                if ($response->statusCode >= 200 && $response->statusCode <= 299) {
                    $response->data = [
                        'success' => true,
                        'status' => $response->statusCode,
                        'data' => $responseData,
                    ];
                } else {
                    $response->data = [
                        'success' => false,
                        'status' => $response->statusCode,
                        'data' => $responseData,
                    ];
                }
                return $response;
            },
        ],

        'i18n' => include __DIR__ . '/components/i18n.php',

        'fs' => getenv('APP_STORAGE_FS') === 'local' ? include __DIR__ . '/components/fs.local.php' :  include __DIR__ . '/components/fs.s3.php',
    ],
    'modules' => [
        'v1' => [
            'class' => 'app\modules\v1\Module',
        ],
    ],
    'params' => $params,
];

if (getenv('YII_ENV_DEV') == 1) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        'allowedIPs' => ['*']
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
