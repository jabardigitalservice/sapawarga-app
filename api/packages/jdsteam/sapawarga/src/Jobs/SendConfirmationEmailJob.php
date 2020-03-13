<?php

namespace Jdsteam\Sapawarga\Jobs;

use app\models\User;
use Yii;
use yii\base\BaseObject;
use yii\queue\JobInterface;

// Queue job to send email in an async way
class SendConfirmationEmailJob extends BaseObject implements JobInterface
{
    public $userId;

    public function execute($queue)
    {
        $user = User::findOne($this->userId);

        $fromEmail = Yii::$app->params['adminEmail'];
        $fromName  = Yii::$app->params['adminEmailName'];

        $confirmURL = \Yii::$app->params['frontendURL'] . '/#/confirm?id=' . $user->id . '&auth_key=' . $user->auth_key;

        $email = \Yii::$app->mailer
            ->compose(
                ['html' => 'email-profile-updated-html'],
                [
                    'appName' => \Yii::$app->name,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'address' => $user->address,
                    'confirmURL' => $confirmURL,
                ]
            )
            ->setTo($user->email)
            ->setFrom([$fromEmail => $fromName])
            ->setSubject('Update Profil dan Verifikasi Email Akun Sapawarga')
            ->send();

        return $email;
    }
}
