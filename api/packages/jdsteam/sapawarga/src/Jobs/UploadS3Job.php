<?php

namespace Jdsteam\Sapawarga\Jobs;

use app\models\User;
use Yii;
use yii\base\BaseObject;
use yii\queue\JobInterface;

/**
 * Queue job upload file to S3 storage.
 *
 * @property string $relative_path
 * @property string $file_path_temp
 * @property string $user_id
 * @property string $email_notif_param      parameter to be passed to GenericEmailJob for success notifcation
 * @property string $job_history_class_name class name for job history log 
 * @property string $history_id             id for the ascociated job history log
 */
class UploadS3Job extends BaseObject implements JobInterface
{
    public $relative_path;
    public $file_path_temp;
    public $user_id;
    public $email_notif_param;
    public $job_history_class_name;
    public $history_id;

    public function execute($queue)
    {
        echo "Uploading to S3 storage" . PHP_EOL;
        $filesystem = Yii::$app->fs;
        $user = User::findOne($this->user_id);
        $job_history = ($this->job_history_class_name)::findOne($this->history_id);

        $stream = fopen($this->file_path_temp, 'r+');
        $publicBaseUrl = Yii::$app->params['storagePublicBaseUrl'];

        $filesystem->writeStream($this->relative_path, $stream);
        // if S3 account does not provide cloudfront for publicly accessing file, 
        // we must manually set public ACL. in that case, use below code instead of above
        //$filesystem->writeStream($relative_path, $stream, [
            //'visibility' => AdapterInterface::VISIBILITY_PUBLIC
        //]);

        $final_url = $publicBaseUrl;
        // if S3 account does not provide cloudfront for publicly accessing file
        // we could use generic amazon s3 url (only if file already has public access ACL)
        // in that case, use below code instead of above
        //$final_url = sprintf('https://%s.s3.%s.amazonaws.com', $filesystem->bucket, $filesystem->region);
        $final_url .= "/$this->relative_path";
        unlink($this->file_path_temp);

        echo "Upload finished. Final url: $final_url" . PHP_EOL;
        $job_history->final_url = $final_url;
        $job_history->save();

        // send result notification to user
        echo "Sending notification email" . PHP_EOL;
        Yii::$app->queue->priority(1000)->push(new GenericEmailJob(array_merge(
            $this->email_notif_param, 
            [
                'destination' => $user->email,
                'content' => [
                    'final_url' => $final_url,
                ],
            ]
        )));
    }

    /* Time To Reserve property. 
     * ref: https://github.com/yiisoft/yii2-queue/blob/master/docs/guide/retryable.md#retry-options 
     * @return int Seconds
     */
    public function getTtr()
    {
        return 10 * 60;
    }

    /** wether current job is still retryable
     * ref: https://github.com/yiisoft/yii2-queue/blob/master/docs/guide/retryable.md#retry-options
     * @return boolean
     */
    public function canRetry($attempt, $error)
    {
        return ($attempt < 3) ;
    }
}
