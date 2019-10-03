<?php

namespace Jdsteam\Sapawarga\Jobs;

use Yii;
use yii\base\BaseObject;
use yii\queue\JobInterface;

class EmailJob extends BaseObject implements JobInterface
{
    public function execute($queue)
    {
        $fromEmail = Yii::$app->params['adminEmail'];
        $fromName  = Yii::$app->params['adminEmailName'];

        Yii::$app->mailer
            ->compose(
                ['html' => 'test-email-html'],
                [
                    'appName' => Yii::$app->name,
                ]
            )
            ->setFrom([$fromEmail => $fromName])
            ->setTo('yohang88@gmail.com')
            ->setSubject('Message subject')
            ->send();
    }
}
