<?php

use creocoder\flysystem\AwsS3Filesystem;

return [
    'class' => AwsS3Filesystem::class,
    'key' => env('APP_STORAGE_S3_KEY'),
    'secret' => env('APP_STORAGE_S3_SECRET'),
    'bucket' => env('APP_STORAGE_S3_BUCKET'),
    'region' => env('APP_STORAGE_S3_BUCKET_REGION'),
    // 'version' => 'latest',
    // 'baseUrl' => 'your-base-url',
    // 'prefix' => 'your-prefix',
    // 'options' => [],
    // 'endpoint' => 'http://my-custom-url'
];
