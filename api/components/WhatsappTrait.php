<?php

namespace app\components;

use Aws\Exception\AwsException;
use Aws\Sqs\SqsClient;
use Yii;

trait WhatsappTrait
{
    public function getClient()
    {
        return new SqsClient([
            'credentials' => [
                'key' => Yii::$app->sqsQueue->key,
                'secret' => Yii::$app->sqsQueue->secret,
            ],
            'region' => Yii::$app->sqsQueue->region,
            'version' => Yii::$app->sqsQueue->version,
        ]);
    }

    public function pushQueue($phoneNumber, $message)
    {
        $messageRequest = [
            'QueueUrl'          => Yii::$app->sqsQueue->url,
            'DelaySeconds'      => 10,
            'MessageAttributes' => [
                'PhoneNumber'   => [
                    'DataType'    => 'String',
                    'StringValue' => $this->cleanPhoneNumber($phoneNumber)
                ]
            ],
            'MessageBody' => $message,
        ];

        try {
            return $this->getClient()->sendMessage($messageRequest);
        } catch (AwsException $e) {
            // output error message if fails
            error_log($e->getMessage());
        }
    }

    public function cleanPhoneNumber($phoneNumber)
    {
        return preg_replace('/^0{1}/', '62', $phoneNumber);
    }
}
