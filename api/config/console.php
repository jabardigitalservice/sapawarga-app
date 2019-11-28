<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic-console',
    'name' => 'Sapawarga',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log', 'queue', 'queueImport'],
    'controllerNamespace' => 'app\commands',
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm' => '@vendor/npm-asset',
        '@tests' => '@app/tests',
    ],
    'components' => [
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'mailer' => include __DIR__ . '/components/mailer.php',
        'queue' => include __DIR__ . '/components/queue.db.php',
        'queueImport' => include __DIR__ . '/components/queue-import.db.php',
        'db' => $db,
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
        ],

        'i18n' => include __DIR__ . '/components/i18n.php',

        'fs' => getenv('APP_STORAGE_FS') === 'local' ? include __DIR__ . '/components/fs.local.php' :  include __DIR__ . '/components/fs.s3.php',
    ],
    'params' => $params,
    'controllerMap' => [
        'migrate' => [
            'class' => 'yii\console\controllers\MigrateController',
            'templateFile' => '@app/components/MigrationTemplate.php',
            'migrationNamespaces' => [
                'yii\queue\db\migrations',
            ],
        ],
        // 'fixture' => [ // Fixture generation command line.
        //     'class' => 'yii\faker\FixtureController',
        // ],
    ],

];

if (getenv('YII_ENV_DEV') == 1) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
