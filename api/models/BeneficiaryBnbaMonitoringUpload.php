<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "beneficiaries_bnba_monitoring_uploads".
 *
 * @property id $id
 * @property integer $code_bps
 * @property string $kabkota_name
 * @property integer $tahap_bantuan
 * @property integer $is_dtks
 * @property integer $last_updated
 * @property integer $created_at
 * @property integer $updated_at
 */

class BeneficiaryBnbaMonitoringUpload extends ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'beneficiaries_bnba_monitoring_uploads';
    }

    /** @inheritdoc */
    public function behaviors()
    {
        return [
            [
                'class'              => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value'              => time(),
            ]
        ];
    }
}
