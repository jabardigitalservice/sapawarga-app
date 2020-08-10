<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\helpers\Json;
use app\models\BeneficiaryBnbaTahapSatu;

/**
 * This is the base class for all DownloadHistory models.
 *
 * @property int $id
 * @property int $user_id
 * @property int $job_id
 * @property int $row_count
 * @property int $row_processed
 * @property int $start_at
 * @property int $done_at
 * @property string $final_url
 * @property json $params
 * @property json $errors Encountered error logs
 */
class BaseDownloadHistory extends ActiveRecord
{
    const STATUS_START = 1;
    const STATUS_SUCCESS = 10;
    const STATUS_ERROR = 20;
    const STATUS_VALIDATION_ERROR = 21;

    private $_job_details;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'queue_details';
    }

    /**
     * {@inheritdoc}
     */
    public function fields()
    {
        $fields = parent::fields();

        $fields['aggregate'] = function ($model) {
            return $model->getAggregateRowProgress();
        };
        $fields['waiting_jobs'] = function ($model) {
            return $model->countJobInLine();
        };
        unset($fields['errors']);
        $fields['error'] = function ($model) {
            return isset($model->logs['errors']) && !empty($model->logs['errors']) && $model->logs['errors'] != null;
        };

        // old fields to maintain backward compatibility with old API
        $fields['final_url'] = function ($model) {
            return $model->results['final_url'];
        };
        $fields['row_processed'] = function ($model) {
            return $model->processed_row;
        };
        $fields['row_count'] = function ($model) {
            return $model->total_row;
        };

        return $fields;
    }

    /** Count number of other queue job waiting in line before this job
     *
     * @return int Number of waiting jobs
     */
    public function countJobInLine()
    {
        return $this->getWaitingListQuery()->count();
    }

    /** Get the aggregate in-progress row count accross all jobs in queue line
     *
     * @return Dict of int 'row_count' & 'row_processed'
     */
    public function getAggregateRowProgress($tag = null)
    {
        $histories = $this->getWaitingListQuery()->all();

        $total_row_count = $this->total_row;
        $total_row_processed = $this->processed_row;
        $start_time = $current_time = time();
        foreach ($histories as $history) {
            $total_row_count += $history->total_row;
            $total_row_processed += $history->processed_row;
            if (!empty($history->start_at)) {
                $start_time = $history->start_at;
            }
        }

        return compact(
            'total_row_count',
            'total_row_processed',
            'start_time',
            'current_time'
        );
    }

    /** Count affected rows in this queue job
     *
     * @return int Number of affected rows
     */
    public function countAffectedRows()
    {
        return $this->getQuery()->count();
    }

    /** Get query builder instance for all waiting jobs
     *
     * @return yii\db\Query
     */
    public function getWaitingListQuery()
    {
        return self::find()
            ->where(['<','id',$this->id])
            ->andWhere([ 'status' => [ self::STATUS_START, null ] ]);
    }


    /** Get query builder instance for curent job parameters
     *
     * @return yii\db\Query
     */
    public function getQuery()
    {
        throw new \Exception('getQuery method must be overwritten by child of BaseDownloadHistory class');
    }

    /**
     * set jobHistory status to started
     */
    public function setStart()
    {
        $this->status = self::STATUS_START;
        $this->notes = 'Export process started';
        $this->start_at = time();
        $this->save();
    }

    /**
     * set jobHistory status to finished
     */
    public function setFinish()
    {
        $this->done_at = time();
        $this->processed_row = $this->total_row;
        $this->status = self::STATUS_SUCCESS;
        $this->notes = 'Success';
        $this->save();
    }

    /**
     * insert new error log to job history error field
     */
    public function setError($error_detail)
    {
        $this->done_at = time();
        $this->status = self::STATUS_ERROR;
        $this->notes = 'Error';

        $logs = ($this->logs) ?: [];
        if (!isset($logs['errors'])) {
            $logs['errors'] = [];
        }
        $logs['errors'][] = $error_detail;
        $this->logs = $logs;
        $this->save();
    }
}
