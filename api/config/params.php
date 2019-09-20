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
    'supportEmail' => 'sapawarga@jabarprov.go.id',
    'adminEmail' => 'sapawarga@jabarprov.go.id',
    'jwtSecretCode' => 'someSecretKey',
    'user.passwordResetTokenExpire' => 3600,
    'paginationParams' => $paginationParams,
    'upload_max_size' => 1024 * 1024 * 2,
    'storageFilesystem' => env('APP_STORAGE_FS'),
    'storagePublicBaseUrl' => env('APP_STORAGE_PUBLIC_URL'),
    'hashidSaltSecret' => 'JDSSaltSecret',
    'hashidLengthPad' => 5,
];
