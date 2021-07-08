<?php

use yii\log\FileTarget;

$params = include __DIR__ . '/params.php';

$config = [
    'id' => 'boilerplate-api-test',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'queue', 'sqsQueue'],
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
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => FileTarget::class,
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'monolog' => include __DIR__ . '/components/monolog.php',
        'queue' => include __DIR__ . '/components/queue.db.php',
        'sqsQueue' => include __DIR__ . '/components/queue.sqs.php',
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
                if ($response->format == 'html') {
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

        'fs' => include __DIR__ . '/components/fs.local.php',
    ],
    'modules' => [
        'v1' => [
            'class' => 'app\modules\v1\Module',
        ],
    ],
    'params' => $params,
];

return $config;
