<?php

namespace Jdsteam\Sapawarga\Jobs;

use Yii;
use yii\base\BaseObject;
use yii\queue\JobInterface;

class EmailJob extends BaseObject implements JobInterface
{
    public function execute($queue)
    {
        Yii::$app->mailer
            ->compose(
                ['html' => 'test-email-html'],
                [
                    'appName' => Yii::$app->name,
                ]
            )
            ->setFrom('from@domain.com')
            ->setTo('to@domain.com')
            ->setSubject('Message subject')
            ->send();
    }
}
