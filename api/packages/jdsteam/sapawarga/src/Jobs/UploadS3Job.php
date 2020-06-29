<?php

namespace Jdsteam\Sapawarga\Jobs;

use app\models\User;
use Yii;
use yii\base\BaseObject;
use yii\queue\RetryableJobInterface;
use Jdsteam\Sapawarga\Jobs\Concerns\HasJobHistory;

/**
 * Queue job upload file to S3 storage.
 *
 * @property string $relativePath
 * @property string $filePathTemp
 * @property string $userId
 * @property string $emailNotifParam      parameter to be passed to GenericEmailJob for success notifcation
 * @property string $jobHistoryClassName  class name for job history log 
 * @property string $historyId            id for the ascociated job history log
 */
class UploadS3Job extends BaseObject implements RetryableJobInterface
{
    use HasJobHistory;

    public $relativePath;
    public $filePathTemp;
    public $userId;
    public $emailNotifParam;

    public function execute($queue)
    {
        echo "Uploading to S3 storage" . PHP_EOL;
        $filesystem = Yii::$app->fs;
        $user = User::findOne($this->userId);

        $stream = fopen($this->filePathTemp, 'r+');
        $publicBaseUrl = Yii::$app->params['storagePublicBaseUrl'];

        $filesystem->writeStream($this->relativePath, $stream);
        // if S3 account does not provide cloudfront for publicly accessing file, 
        // we must manually set public ACL. in that case, use below code instead of above
        //$filesystem->writeStream($relativePath, $stream, [
            //'visibility' => AdapterInterface::VISIBILITY_PUBLIC
        //]);

        $final_url = $publicBaseUrl;
        // if S3 account does not provide cloudfront for publicly accessing file
        // we could use generic amazon s3 url (only if file already has public access ACL)
        // in that case, use below code instead of above
        //$final_url = sprintf('https://%s.s3.%s.amazonaws.com', $filesystem->bucket, $filesystem->region);
        $final_url .= "/$this->relativePath";
        unlink($this->filePathTemp);

        echo "Upload finished. Final url: $final_url" . PHP_EOL;
        $jobHistory = $this->jobHistory;
        $jobHistory->final_url = $final_url;
        $jobHistory->save();

        // send result notification to user
        echo "Sending notification email" . PHP_EOL;
        Yii::$app->queue->priority(1000)->push(new GenericEmailJob(array_merge(
            $this->emailNotifParam, 
            [
                'jobHistoryClassName' => $this->jobHistoryClassName,
                'historyId' => $this->historyId,
                'destination' => $user->email,
                'content' => [
                    'final_url' => $final_url,
                ],
            ]
        )));
    }

    /**
     * {@inheritdoc}
     */
    public function getTtr()
    {
        return 10 * 60;
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
