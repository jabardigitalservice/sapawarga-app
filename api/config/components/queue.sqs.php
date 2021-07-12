<?php

return [
    'class' => \yii\queue\sqs\Queue::class,
    'url' => getenv('AWS_WHATSAPP_URL'), //SQS URL
    'key' => getenv('AWS_KEY'), // SQS Key
    'secret' => getenv('AWS_SECRET'), // SQS Secret
    'region' => getenv('AWS_REGION'), // SQS Region
];
