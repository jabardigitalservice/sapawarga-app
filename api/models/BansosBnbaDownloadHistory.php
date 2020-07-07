<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\helpers\Json;
use app\models\BeneficiaryBnbaTahapSatu;

/**
 * This is the model class for table "bansos_bnba_download_histories".
 *
 * {@inheritdoc}
 */
class BansosBnbaDownloadHistory extends BaseDownloadHistory
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'bansos_bnba_download_histories';
    }

    /** Get query builder instance for curent job parameters
     *
     * @return yii\db\Query
     */
    public function getQuery()
    {
        return BeneficiaryBnbaTahapSatu::find()
            ->where($this->params)
            ->andWhere(['or',
                ['is_deleted' => null],
                ['is_deleted' => 0]
            ]);
    }
}
