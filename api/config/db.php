<?php

return [
    'class' => 'yii\db\Connection',

    // configuration for master
    'dsn' => 'mysql:host=' . getenv('MYSQL_HOST')
        . ';port=' . getenv('MYSQL_PORT')
        . ';dbname=' . getenv('MYSQL_DATABASE'),
    'username' => getenv('MYSQL_USER'),
    'password' => getenv('MYSQL_PASSWORD'),
    'charset' => 'utf8mb4',

    // configuration for slaves
    'slaveConfig' => [
        'username' => getenv('MYSQL_USER_SLAVE'),
        'password' => getenv('MYSQL_PASSWORD_SLAVE'),
        'attributes' => [
            // use a smaller connection timeout
            PDO::ATTR_TIMEOUT => 30
        ],
    ],

    // list of slave configurations
    'slaves' => [
        [
            'dsn' => 'mysql:host=' . getenv('MYSQL_HOST_SLAVE_1') .
                  ';port=' . getenv('MYSQL_PORT') .
                  ';dbname=' . getenv('MYSQL_DATABASE')
        ],
        [
            'dsn' => 'mysql:host=' . getenv('MYSQL_HOST_SLAVE_2') .
                  ';port=' . getenv('MYSQL_PORT') .
                  ';dbname=' . getenv('MYSQL_DATABASE')
        ],
        [
            'dsn' => 'mysql:host=' . getenv('MYSQL_HOST_SLAVE_3') .
                  ';port=' . getenv('MYSQL_PORT') .
                  ';dbname=' . getenv('MYSQL_DATABASE')
        ]
    ],
];
