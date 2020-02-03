<?php

$db = [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host=' . getenv('MYSQL_HOST')
        . ';port=' . getenv('MYSQL_PORT')
        . ';dbname=' . getenv('MYSQL_DATABASE'),
    'username' => getenv('MYSQL_USER'),
    'password' => getenv('MYSQL_PASSWORD'),
    'charset' => 'utf8mb4',
];

if (getenv('YII_ENV_DEV') != 1) {
    // Caching options (for production environment)
    $schemaCache = [
        'enableSchemaCache' => true,
        'schemaCacheDuration' => 3600,
        'schemaCache' => 'cache',
    ];
    $queryCache = [
        'enableQueryCache' => true,
        'queryCacheDuration' => 3600,
        'queryCache' => 'cache',
    ];

    $db = array_merge($db, $schemaCache, $queryCache);
}

return $db;
