<?php

return [
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
];
