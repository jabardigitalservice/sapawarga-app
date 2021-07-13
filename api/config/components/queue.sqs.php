<?php

return [
    'class' => \yii\queue\sqs\Queue::class,
    'url' => getenv('AWS_WHATSAPP_URL'), //SQS URL
    'key' => getenv('APP_STORAGE_S3_KEY'), // SQS Key
    'secret' => getenv('APP_STORAGE_S3_SECRET'), // SQS Secret
    'region' => getenv('APP_STORAGE_S3_BUCKET_REGION'), // SQS Region
];
