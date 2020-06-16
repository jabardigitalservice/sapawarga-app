<?php

namespace Jdsteam\Sapawarga\Jobs;

use app\models\User;
use Yii;
use yii\base\BaseObject;
use yii\queue\JobInterface;

// Queue job to send email in an async way
class GenericEmailJob extends BaseObject implements JobInterface
{
    public $destination;
    public $template;
    public $content;
    public $subject;

    public function execute($queue)
    {
        $fromEmail = Yii::$app->params['adminEmail'];
        $fromName  = Yii::$app->params['adminEmailName'];

        echo "Sending email to {$this->destination}" . PHP_EOL;

        $email = \Yii::$app->mailer
            ->compose($this->template, $this->content)
            ->setTo($this->destination)
            ->setFrom([$fromEmail => $fromName])
            ->setSubject($this->subject)
            ->send();

        return $email;
    }

    public function getTtr()
    {
        return 15 * 60;
    }

    public function canRetry($attempt, $error)
    {
        return ($attempt < 3);
    }
}
