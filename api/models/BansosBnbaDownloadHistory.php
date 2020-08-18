<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Json;
use app\models\BeneficiaryBnbaTahapSatu;
use Jdsteam\Sapawarga\Jobs\ExportBnbaJob;
use Jdsteam\Sapawarga\Jobs\ExportBnbaWithComplainJob;

/**
 * This is the model class for table "bansos_bnba_download_histories".
 *
 * {@inheritdoc}
 * @property int $export_type
 */
class BansosBnbaDownloadHistory extends BaseDownloadHistory
{
    const TYPE_BNBA_ORIGINAL = 'bnba'; // original ExportBnba job type
    const TYPE_BNBA_WITH_COMPLAIN = 'bnbawithcomplain'; // export type which include joined data from `beneficiaries_complain` table

    const AVAILABLE_TYPES = [
      self::TYPE_BNBA_ORIGINAL => 'Original Template',
      self::TYPE_BNBA_WITH_COMPLAIN => 'Template With Complain Notes',
    ];

    /** Get query builder instance for curent job parameters
     *
     * @return yii\db\Query
     */
    public function getQuery()
    {
        $queryParams = $this->params;

        // WHERE query order is importand in order to gain indexing improvement
        $query = BeneficiaryBnbaTahapSatu::find()
            ->where([ 'is_deleted' => 1 ]);

        // special filter for export with complain,
        if ($this->job_type == self::TYPE_BNBA_WITH_COMPLAIN) {
            $query = $query->andWhere([
                'is_dtks' => 0,
                'id_tipe_bansos' => [6, 16], // pintu banprov non-dtks
            ]);
        }

        $query = $query->andWhere($queryParams);
        return $query;
    }

    /** Start Export Bnba Job according to type
     *
     * @return None
     */
    public function startJob()
    {
        switch ($this->job_type) {
            case self::TYPE_BNBA_WITH_COMPLAIN :
                $job_id = Yii::$app->queue->priority(100)->push(new ExportBnbaWithComplainJob([
                    'userId' => $this->user_id,
                    'historyId' => $this->id,
                ]));
                break;
            default:
                $job_id = Yii::$app->queue->priority(200)->push(new ExportBnbaJob([
                    'userId' => $this->user_id,
                    'historyId' => $this->id,
                ]));
        }

        $logs = ($this->logs) ?: [];
        $logs['job_id'] = $job_id;
        $this->logs = $logs;
        $this->save();
    }
}
