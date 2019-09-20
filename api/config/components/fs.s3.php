<?php

use creocoder\flysystem\AwsS3Filesystem;

return [
    'class' => AwsS3Filesystem::class,
    'key' => env('S3_KEY'),
    'secret' => env('S3_SECRET'),
    'bucket' => env('S3_BUCKET'),
    'region' => env('S3_BUCKET_REGION'),
    // 'version' => 'latest',
    // 'baseUrl' => 'your-base-url',
    // 'prefix' => 'your-prefix',
    // 'options' => [],
    // 'endpoint' => 'http://my-custom-url'
];
