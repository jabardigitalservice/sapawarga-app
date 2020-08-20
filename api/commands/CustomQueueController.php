<?php
/*
 * Usage: yii custom-queue/run-single --queue_id=1234
 *
 */
namespace app\commands;

use Yii;
use yii\db\Query;
use yii\console\Controller;
use yii\console\Exception;
use yii\queue\ExecEvent;

class CustomQueueController extends Controller
{
    private $_queue = null;

    public $queue_id = null;
    public $queue_details_id = null;
    public $job_type = null;

    public function options($actionsID)
    {
        return [
            'queue_id',
            'queue_details_id',
            'job_type',
        ];
    }

    /*
     * Get queue_id regardles which of queue_id or queue_details_id is supplied
     * to the command
     */
    public function getQueueId()
    {
        if (!empty($this->queue_id)) {
            return $this->queue_id;
        } elseif (!empty($this->queue_details_id)) {
            //echo "queue_details_id : {$this->queue_details_id}\n";
            $queue_details = (new Query())
                ->from('queue_details')
                ->where(['id' => $this->queue_details_id ])
                ->one();
            if (!$queue_details) {
                throw new Exception("queue_details_id {$this->queue_details_id} not found");
            }
            //echo json_encode( $queue_details);
            $queue_details_log = json_decode($queue_details['logs'], true);
            return $queue_details_log['job_id'];
        } else {
            throw new Exception('queue_id or queue_details_id is required');
        }
    }

    public function getJobType()
    {
        if (!empty($this->job_type)) {
            return $this->job_type;
        } else {
            throw new Exception('job_type parameter is required');
        }
    }

    /*
     * Get queue object used on this application
     */
    public function getQueue()
    {
        if (empty($this->_queue)) {
            $this->_queue = Yii::$app->queue;
        }

        return $this->_queue;
    }

    /**
     * This method is originally a private method in yii\queue\Queue. Because
     * its protected, we cannot access it from outside here. so we need to
     * re-declare it here
     *
     * @param string $id of a job message
     * @param string $message
     * @param int $ttr time to reserve
     * @param int $attempt number
     * @return bool
     */
    protected function handleMessage($id, $message, $ttr, $attempt)
    {
        $queue = $this->getQueue();
        list($job, $error) = $queue->unserializeMessage($message);
        $event = new ExecEvent([
            'id' => $id,
            'job' => $job,
            'ttr' => $ttr,
            'attempt' => $attempt,
            'error' => $error,
        ]);
        $queue->trigger($queue::EVENT_BEFORE_EXEC, $event);
        if ($event->handled) {
            return true;
        }
        if ($event->error) {
            return $queue->handleError($event);
        }
        try {
            $event->result = $event->job->execute($queue);
        } catch (\Exception $error) {
            $event->error = $error;
            return $queue->handleError($event);
        } catch (\Throwable $error) {
            $event->error = $error;
            return $queue->handleError($event);
        }
        $queue->trigger($queue::EVENT_AFTER_EXEC, $event);
        return true;
    }

    /**
     * This method is originally a private method in yii\queue\Queue. Because
     * its protected, we cannot access it from outside here. so we need to
     * re-declare it here
     * @param array $payload
     */
    protected function release($payload)
    {
        $queue = $this->getQueue();
        if ($queue->deleteReleased) {
            $queue->db->createCommand()->delete(
                $queue->tableName,
                ['id' => $payload['id']]
            )->execute();
        } else {
            $queue->db->createCommand()->update(
                $queue->tableName,
                ['done_at' => time()],
                ['id' => $payload['id']]
            )->execute();
        }
    }

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
        $queue = $this->getQueue();
        return $queue->db->useMaster(function () use ($queue, $query) {
            if (!$queue->mutex->acquire(__CLASS__ . $queue->channel, $queue->mutexTimeout)) {
                throw new Exception('Has not waited the lock.');
            }

            $payload = $query->one($queue->db);

            if (!$payload) {
                throw new Exception("Query return empty");
            }

            if (is_array($payload)) {
                if (!empty($payload['reserved_at'])) {
                    throw new Exception('Queue job '.$payload['id'].' already running');
                }

                try {
                    //TODO: check again is this function really could be commented
                    //$queue->moveExpired();

                    // Reserve one message
                    $payload['reserved_at'] = time();
                    $payload['attempt'] = (int) $payload['attempt'] + 1;
                    $queue->db->createCommand()->update($queue->tableName, [
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
                    $queue->mutex->release(__CLASS__ . $queue->channel);
                }

            }
            return $payload;
        });
    }

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

    public function actionRunSingle()
    {
        $queueId = $this->getQueueId();

        echo "Searching for QueueID: $queueId\n";

        $query = (new Query())
                ->from('queue')
                ->andWhere(['id' => $queueId ]);

        $this->runSingle($query);
    }

    public function actionRunQueueByType()
    {
        $jobType = $this->getJobType();

        echo "Searching for queue with type: $jobType\n";

        $query = (new Query())
                ->select(['queue.*'])
                ->from(['queue', 'queue_details'])
                ->andWhere(['queue_details.job_type' => $jobType ])
                ->andWhere(['queue_details.logs.job_id = queue.id' ])
                ->andWhere(['queue.channel' => $this->channel, 'queue.reserved_at' => null])
                ->andWhere('queue.[[pushed_at]] <= :time - [[delay]]', [':time' => time()])
                ->orderBy(['queue.priority' => SORT_ASC, 'queue.id' => SORT_ASC])
                ->limit(1);

        $this->runSingle($query);
    }

}
