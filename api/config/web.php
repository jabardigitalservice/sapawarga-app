<?php

$params = include __DIR__ . '/params.php';

$config = [
    'id' => 'boilerplate-api',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'queue'],
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
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => getenv('MAILER_TRANSPORT_FILE'),
            'transport' => [
                'class' => 'Swift_SmtpTransport',
                'host' => getenv('MAILER_HOST'),
                'username' => getenv('MAILER_USER'),
                'password' => getenv('MAILER_PASSWORD'),
                'port' => getenv('MAILER_PORT'),
                'encryption' => getenv('MAILER_ENCRYPTION'),
            ],
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'queue' => include __DIR__ . '/queue.php',
        'db' => include __DIR__ . '/db.php',

        'urlManager' => [
            'baseUrl' => '/',    // Added for
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => true,
            'rules' => [
                'ping' => 'site/ping',
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/user',
                    'pluralize' => false,
                    'tokens' => [
                        '{id}' => '<id:\d+>',
                    ],
                    'extraPatterns' => [
                        'OPTIONS {id}' => 'options',
                        'POST login' => 'login',
                        'OPTIONS login' => 'options',
                        'POST logout' => 'logout',
                        'OPTIONS logout' => 'options',
                        'POST signup' => 'signup',
                        'OPTIONS signup' => 'options',
                        'POST confirm' => 'confirm',
                        'OPTIONS confirm' => 'options',
                        'POST password-reset-request' => 'password-reset-request',
                        'OPTIONS password-reset-request' => 'options',
                        'POST password-reset-token-verification' => 'password-reset-token-verification',
                        'OPTIONS password-reset-token-verification' => 'options',
                        'POST password-reset' => 'password-reset',
                        'OPTIONS password-reset' => 'options',
                        'GET me' => 'me',
                        'POST me' => 'me-update',
                        'OPTIONS me' => 'options',
                        'GET me/photo' => 'me-photo',
                        'POST me/photo' => 'me-photo-upload',
                        'OPTIONS me/photo' => 'options',
                    ]
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/staff',
                    'pluralize' => false,
                    'tokens' => [
                        '{id}' => '<id:\d+>',
                    ],
                    'extraPatterns' => [
                        'OPTIONS {id}' => 'options',
                        'POST login' => 'login',
                        'OPTIONS login' => 'options',
                        'GET count' => 'count',
                        'OPTIONS count' => 'options',
                        'GET get-permissions' => 'get-permissions',
                        'OPTIONS get-permissions' => 'options',
                        'POST photo' => 'photo-upload',
                        'OPTIONS photo' => 'options',
                        'GET me' => 'me',
                        'POST me' => 'me-update',
                        'OPTIONS me' => 'options',
                    ]
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/setting',
                    'pluralize' => false,
                    'tokens' => [
                        '{id}' => '<id:\d+>',
                    ],
                    'extraPatterns' => [
                        'GET public' => 'public',
                        'OPTIONS public' => 'options',
                    ]
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/area',
                    'tokens' => [
                        '{id}' => '<id:\d+>',
                    ],
                    'extraPatterns' => []
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/phone-book',
                    'tokens' => [
                        '{id}' => '<id:\d+>',
                    ],
                    'extraPatterns' => [
                        'GET check-exist' => 'check-exist',
                        'OPTIONS check-exist' => 'options',
                        'GET by-user-location' => 'by-user-location',
                        'OPTIONS by-user-location' => 'options',
                    ]
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/attachment',
                    'tokens' => [],
                    'extraPatterns' => []
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/category',
                    'tokens' => [
                        '{id}' => '<id:\d+>',
                    ],
                    'extraPatterns' => [
                        'GET types' => 'types',
                        'OPTIONS types' => 'options',
                    ]
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/broadcast',
                    'tokens' => [
                        '{id}' => '<id:\d+>',
                    ],
                    'extraPatterns' => []
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/aspirasi',
                    'pluralize' => false,
                    'tokens' => [
                        '{id}' => '<id:\d+>',
                    ],
                    'extraPatterns' => [
                        'GET me' => 'me',
                        'OPTIONS me' => 'options',
                        'POST likes/{id}' => 'likes',
                        'OPTIONS likes/{id}' => 'options',
                        'POST approval/{id}' => 'approval',
                        'OPTIONS approval/{id}' => 'options',
                    ]
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/polling',
                    'pluralize' => false,
                    'tokens' => [
                        '{id}' => '<id:\d+>',
                        '{answerId}' => '<answerId:\d+>',
                    ],
                    'extraPatterns' => [
                        'POST {id}/answers' => 'answer-create',
                        'OPTIONS {id}/answers' => 'options',
                        'PUT {id}/answers/{answerId}' => 'answer-update',
                        'DELETE {id}/answers/{answerId}' => 'answer-delete',
                        'OPTIONS {id}/answers/{answerId}' => 'options',
                        'GET {id}/vote' => 'vote-check',
                        'PUT {id}/vote' => 'vote',
                        'OPTIONS {id}/vote' => 'options',
                    ]
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/survey',
                    'pluralize' => false,
                    'tokens' => [
                        '{id}' => '<id:\d+>',
                    ],
                    'extraPatterns' => []
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/news-channel',
                    'tokens' => [
                        '{id}' => '<id:\d+>',
                    ],
                    'extraPatterns' => []
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/news',
                    'pluralize' => false,
                    'tokens' => [
                        '{id}' => '<id:\d+>',
                    ],
                    'extraPatterns' => [
                        'GET featured' => 'featured',
                        'OPTIONS featured' => 'options',
                        'GET related' => 'related',
                        'OPTIONS related' => 'options',
                        'GET stats/channel' => 'stats-channel',
                        'OPTIONS stats/channel' => 'options',
                        'GET statistics' => 'statistics',
                        'OPTIONS statistics' => 'options',
                    ]
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/video',
                    'pluralize' => true,
                    'tokens' => [
                        '{id}' => '<id:\d+>',
                    ],
                    'extraPatterns' => [
                        'GET statistics' => 'statistics',
                        'OPTIONS statistics' => 'options',
                    ]
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/notification',
                    'tokens' => [
                        '{id}' => '<id:\d+>',
                    ],
                    'extraPatterns' => []
                ],
                [
                    'class' => 'yii\rest\UrlRule',
                    'controller' => 'v1/page',
                    'pluralize' => false,
                    'tokens' => [
                    ],
                    'extraPatterns' => [
                        'GET sse' => 'sse',
                        'OPTIONS sse' => 'sse',
                    ]
                ],
            ]
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
        'i18n' => [
            'translations' => [
                '*' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'sourceLanguage' => 'key',
                    'forceTranslation' => true,
                    'fileMap' => [
                        'app' => 'app.php'
                    ],
                ],
            ],
        ],

        // Other adapter: Local, SFTP, Amazon, Mongo, dsb,
        // please read https://github.com/yii2tech/file-storage
        'fileStorage' => [
            'class' => 'yii2tech\filestorage\local\Storage',
            'basePath' => '@webroot/storage',
            'baseUrl' => $params['local_storage_base_url'] . '/storage',
            'dirPermission' => 0777,
            'filePermission' => 0644,
            'buckets' => [
                'tempFiles' => [
                    'baseSubPath' => 'temp',
                ],
                'imageFiles' => [
                    'baseSubPath' => 'image',
                ],
            ]
        ],
    ],
    'modules' => [
        'v1' => [
            'class' => 'app\modules\v1\Module',
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
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
