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
use yii\queue\db\Queue as BaseDbQueue;

/**
 * Custom queue object used to override many private method in default Queue
 * implementations
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
                throw new Exception("Query return empty");
            }

            if (is_array($payload)) {
                if (!empty($payload['reserved_at'])) {
                    throw new Exception('Queue job '.$payload['id'].' already running');
                }

                try {
                    //TODO: check again is this function really could be commented
                    //$this->moveExpired();

                    // Reserve one message
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
     * Simple wrapper for handleMessage method which is private
     */
    public function handleMessage($id, $message, $ttr, $attempt)
    {
        return parent::handleMessage($id, $message, $ttr, $attempt);
    }

    /**
     * Simple wrapper for handleMessage method which is private
     */
    public function release($payload)
    {
        return parent::release($payload);
    }

}

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

    /**
     * get jobType parameter
     */
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
            $queue = Yii::$app->queue;

            // create new CustomQueue from properties of existing Queue
            $this->_queue = new CustomQueue(get_object_vars($queue));
        }

        return $this->_queue;
    }

    /**
     * Run a single queue job by $query
     *
     * @param yii\db\Query $query Query Builder object to run to get the desired queue job entry
     */
    public function runSingle($query)
    {
        $queue = $this->getQueue();

        if ($payload = $queue->reserveSingle($query)) {
            if ($queue->handleMessage(
                $payload['id'],
                $payload['job'],
                $payload['ttr'],
                $payload['attempt']
            )) {
                $queue->release($payload);
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

    public function actionRunByType()
    {
        $queue = $this->getQueue();
        $jobType = $this->getJobType();

        echo "Searching for queue with type: $jobType\n";

        $query = (new Query())
                ->select(['queue.*'])
                ->from(['queue', 'queue_details'])
                ->andWhere(['queue_details.job_type' => $jobType ])
                ->andWhere(['queue_details.logs.job_id = queue.id' ])
                ->andWhere(['queue.channel' => $queue->channel, 'queue.reserved_at' => null])
                ->andWhere('queue.[[pushed_at]] <= :time - [[delay]]', [':time' => time()])
                ->orderBy(['queue.priority' => SORT_ASC, 'queue.id' => SORT_ASC])
                ->limit(1);

        $this->runSingle($query);
    }

}
