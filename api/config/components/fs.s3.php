<?php

use creocoder\flysystem\AwsS3Filesystem;

return [
    'class' => AwsS3Filesystem::class,
    'key' => getenv('APP_STORAGE_S3_KEY'),
    'secret' => getenv('APP_STORAGE_S3_SECRET'),
    'bucket' => getenv('APP_STORAGE_S3_BUCKET'),
    'region' => getenv('APP_STORAGE_S3_BUCKET_REGION'),
    // 'version' => 'latest',
    // 'baseUrl' => 'your-base-url',
    // 'prefix' => 'your-prefix',
    // 'options' => [],
    // 'endpoint' => 'http://my-custom-url'
];
