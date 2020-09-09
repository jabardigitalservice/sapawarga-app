<?php

namespace Jdsteam\Sapawarga\Jobs\Concerns;

/**
 * this class use any Job History classes
 */
trait HasJobHistory
{
    public $historyId;
    public $jobHistoryClassName;
    private $_jobHistory;

    public function getJobHistory()
    {
        if (empty($this->_job_history)) {
            $this->_jobHistory = ($this->jobHistoryClassName)::findOne($this->historyId);
        }
        return $this->_jobHistory;
    }

    /**
     * insert new error log to job history error field
     */
    public function addErrorLog($attempt, $error)
    {
        $jobHistory = $this->jobHistory;
        $jobHistory->setError([
            'timestamp' => time(),
            'attempt' => $attempt,
            'detail' => (string)$error,
        ]);
    }

}
