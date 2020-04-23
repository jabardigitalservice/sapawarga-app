<?php

$paginationParams = [
    'pageParam',
    'pageSizeParam',
    'params',
    'totalCount',
    'defaultPageSize',
    'pageSizeLimit'
];

return [
    'frontendURL' => getenv('FRONTEND_URL'),
    'supportEmail' => getenv('MAILER_FROM_EMAIL'),
    'adminEmail' => getenv('MAILER_FROM_EMAIL'),
    'adminEmailName' => getenv('MAILER_FROM_NAME'),
    'jwtSecretCode' => 'someSecretKey',
    'user.passwordResetTokenExpire' => 3600,
    'paginationParams' => $paginationParams,
    'upload_max_size' => 1024 * 1024 * 10,
    'storagePublicBaseUrl' => getenv('APP_STORAGE_PUBLIC_URL'),
    'hashidSaltSecret' => 'JDSSaltSecret',
    'hashidLengthPad' => 5,
    'userImportMaximumRows' => 5000,
];
