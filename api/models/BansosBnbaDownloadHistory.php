<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\helpers\Json;
use app\models\BeneficiaryBnbaTahapSatu;

/**
 * This is the model class for table "bansos_bnba_download_histories".
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
class BansosBnbaDownloadHistory extends ActiveRecord
{
    private $_job_details;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'bansos_bnba_download_histories';
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
            return !empty($model->errors) && $model->errors != null;
        };

        return $fields;
    }

    /** Count number of other queue job waiting in line before this job
     *
     * @return int Number of waiting jobs
     */
    public function countJobInLine()
    {
        return self::find()
            ->where(['<','id',$this->id])
            ->andWhere([
               'done_at' => null,
            ])
            ->count();
    }

    /** Get the aggregate in-progress row count accross all jobs in queue line
     *
     * @return Dict of int 'row_count' & 'row_processed'
     */
    public function getAggregateRowProgress($tag = null)
    {
        $histories = self::find()
            ->where(['<','id',$this->id])
            ->andWhere([
               'done_at' => null,
            ])
            ->orWhere(['id' => $this->id]) // make sure current job always selected
            ->all();
        
        $total_row_count = 0;
        $total_row_processed = 0;
        $start_time = $current_time = time();
        foreach ($histories as $history) {
            $total_row_count += $history->row_count;
            $total_row_processed += $history->row_processed;
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
        return BeneficiaryBnbaTahapSatu::find()
            ->where($this->params)
            ->count();
    }
}
