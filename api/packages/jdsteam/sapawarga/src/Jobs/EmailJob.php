<?php

namespace Jdsteam\Sapawarga\Jobs;

use yii\base\BaseObject;
use yii\queue\JobInterface;

// Queue job to send email in an async way
class EmailJob extends BaseObject implements JobInterface
{
    public $user;
    public $email;

    public function execute($queue)
    {
        $confirmURL = \Yii::$app->params['frontendURL'] . '#/confirm?id=' . $this->user->id . '&auth_key=' . $this->user->auth_key;

        $email = \Yii::$app->mailer
            ->compose(
                ['html' => 'email-profile-updated-html'],
                [
                    'appName' => \Yii::$app->name,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                    'phone' => $this->user->phone,
                    'address' => $this->user->address,
                    'confirmURL' => $confirmURL,
                ]
            )
            ->setTo($this->email)
            ->setFrom([\Yii::$app->params['supportEmail'] => \Yii::$app->name])
            ->setSubject('Email confirmation')
            ->send();

        return $email;
    }
}
