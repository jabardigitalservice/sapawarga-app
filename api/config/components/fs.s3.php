<?php

use creocoder\flysystem\AwsS3Filesystem;

return [
    'class' => AwsS3Filesystem::class,
    'key' => 'your-key',
    'secret' => 'your-secret',
    'bucket' => 'your-bucket',
    'region' => 'your-region',
    // 'version' => 'latest',
    // 'baseUrl' => 'your-base-url',
    // 'prefix' => 'your-prefix',
    // 'options' => [],
    // 'endpoint' => 'http://my-custom-url'
];
