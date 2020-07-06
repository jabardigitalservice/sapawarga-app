<?php

namespace Jdsteam\Sapawarga\Jobs;

use app\models\User;
use Yii;
use yii\base\BaseObject;
use yii\queue\RetryableJobInterface;
use Jdsteam\Sapawarga\Jobs\Concerns\HasJobHistory;

/**
 * Queue job upload file to S3 storageQueue job to send email in an async way.
 *
 * @property string $destination
 * @property string $template
 * @property string $content
 * @property string $subject 
 * @property string $jobHistoryClassName  class name for job history log 
 * @property string $historyId            id for the ascociated job history log
 */
class GenericEmailJob extends BaseObject implements RetryableJobInterface
{
    use HasJobHistory;

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

    /**
     * {@inheritdoc}
     */
    public function getTtr()
    {
        return 15 * 60;
    }

    /**
     * {@inheritdoc}
     */
    public function canRetry($attempt, $error)
    {
        $this->addErrorLog($attempt, $error);

        return ($attempt < 3);
    }
}
