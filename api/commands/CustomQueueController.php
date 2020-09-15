<?php
/*
 * Usage:
 *
 * to run a single job by its queue id or queue_details id , use:
 *
 *    yii custom-queue/run-single --queue_id=1234 or --queue_details_id=1234
 *
 * to run a single job in queue by job_type, use:
 *
 *    yii custom-queue/run-by-type <job_type> <number of job to run (default 1)> <delay in seconds (default=3)>
 *
 *    notes: to make the worker run indefinitely, use limit = 0.
 */
namespace app\commands;

use app\components\CustomQueue;
use Yii;
use yii\db\Query;
use yii\console\Controller;
use yii\console\Exception;

class CustomQueueController extends Controller
{
    public $queue_id = null;
    public $queue_details_id = null;

    /**
     * @inheritdoc
     */
    public function options($actionsID)
    {
        return [
            'queue_id',
            'queue_details_id',
        ];
    }

    /**
     * @inheritdoc
     */
    public function runSingle($query)
    {
        // create new CustomQueue from properties of existing Queue
        $queueProps = get_object_vars(Yii::$app->queue);
        $queue = new CustomQueue($queueProps);

        $queue->runSingle($query);
    }

    public function actionRunSingle()
    {
        if (!empty($this->queue_id)) {
            $queueId =  $this->queue_id;
        } elseif (!empty($this->queue_details_id)) {
            $queue_details = (new Query())
                ->select(['JSON_UNQUOTE(JSON_EXTRACT(logs, "$.job_id")) as job_id'])
                ->from('queue_details')
                ->where(['id' => $this->queue_details_id ])
                ->one();
            if (!$queue_details) {
                throw new Exception("queue_details_id {$this->queue_details_id} not found");
            }
            $queueId =  $queue_details['job_id'];
        } else {
            throw new Exception('queue_id or queue_details_id is required');
        }

        echo "Searching for QueueID: $queueId\n";

        $query = (new Query())
                ->from('queue')
                ->andWhere(['id' => $queueId ]);

        $this->runSingle($query);
    }

    /**
     * Run job in queue, filtered by job_type
     *
     * @param string $jobType name of job_type to be filtered
     * @param int $limit number of job to be run. set to 0 for infinitely looping
     * @param int $delay number of seconds between each loop
     */
    public function actionRunByType($jobType = null, $limit = 1, $delay = 3)
    {
        if (empty($jobType)) {
            throw new Exception('job_type parameter is required');
        }

        $query = (new Query())
                ->select(['queue.*'])
                ->from(['queue', 'queue_details'])
                ->andWhere(['queue_details.job_type' => $jobType ])
                ->andWhere(['queue_details.status' => null ])
                ->andWhere('queue.id = JSON_UNQUOTE(queue_details.logs->"$.job_id")')
                ->orderBy(['queue.priority' => SORT_ASC, 'queue.id' => SORT_ASC])
                ->limit(1);

        // execution loop
        $step = $limit;
        while ($step || $limit == 0) {
            echo "Searching for queue job with type: $jobType\n";

            try {
                $this->runSingle($query);
            } catch (Exception $e) {
                echo "Error: {$e->getMessage()}\n";
            }

            $step--;

            if ($step) {
                sleep($delay);
            }
        }
    }
}
