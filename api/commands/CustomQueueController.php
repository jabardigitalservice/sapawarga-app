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

use Yii;
use yii\db\Query;
use yii\console\Controller;
use yii\console\Exception;
use yii\queue\ExecEvent;
use yii\queue\db\Queue as BaseDbQueue;

/**
 * Custom queue object implementation specific for our use case
 */
class CustomQueue extends BaseDbQueue
{
    /**
     * Modification of reserve method in yii\queue\driver\db\Queue to enable
     * executing a specific queue job by queueId parameter
     *
     * @param yii\db\Query $query Query Builder object to be run
     * @return array|false payload
     * @throws Exception in case it hasn't waited the lock
     */
    public function reserveSingle($query)
    {
        return $this->db->useMaster(function () use ($query) {
            if (!$this->mutex->acquire(__CLASS__ . $this->channel, $this->mutexTimeout)) {
                throw new Exception('Has not waited the lock.');
            }

            $payload = $query->one($this->db);

            if (!$payload) {
                $this->mutex->release(__CLASS__ . $this->channel);
                throw new Exception('Queue job not found');
            }

            if (is_array($payload)) {
                if (!empty($payload['reserved_at'])) {
                    $this->mutex->release(__CLASS__ . $this->channel);
                    throw new Exception('Queue job ' . $payload['id'] . ' already running');
                }

                try {
                    //TODO: check again is this function really could be commented
                    //$this->moveExpired();

                    // Reserve one message
                    echo 'Reserving job id ' . $payload['id'] . PHP_EOL;
                    $payload['reserved_at'] = time();
                    $payload['attempt'] = (int) $payload['attempt'] + 1;
                    $this->db->createCommand()->update($this->tableName, [
                        'reserved_at' => $payload['reserved_at'],
                        'attempt' => $payload['attempt'],
                    ], [
                        'id' => $payload['id'],
                    ])->execute();

                    // pgsql
                    if (is_resource($payload['job'])) {
                        $payload['job'] = stream_get_contents($payload['job']);
                    }
                } finally {
                    $this->mutex->release(__CLASS__ . $this->channel);
                }
            }

            return $payload;
        });
    }

    /**
     * Run a single queue job by $query. This is a modification from run() method
     * in yii\queue\db\src\Queue
     *
     * @param yii\db\Query $query Query Builder object to run to get the desired queue job entry
     */
    public function runSingle($query)
    {
        if ($payload = $this->reserveSingle($query)) {
            if ($this->handleMessage(
                $payload['id'],
                $payload['job'],
                $payload['ttr'],
                $payload['attempt']
            )) {
                $this->release($payload);
            }
        }
    }
}

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
                ->select(['logs->"$.job_id" as job_id'])
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
