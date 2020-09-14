<?php

namespace app\components;

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
