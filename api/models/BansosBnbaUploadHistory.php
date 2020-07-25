<?php

namespace app\models;

use yii\db\ActiveRecord;
use yii\helpers\Json;

/**
 * This is the model class for table "bansos_bnba_upload_noimport_histories".
 *
 * {@inheritdoc}
 */
class BansosBnbaUploadHistory extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'bansos_bnba_upload_noimport_histories';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [[
                'user_id',
                'kabkota_name',
                'original_filename',
                'final_url',
                'timestamp',
                'status',
            ], 'safe'],
        ];
    }

}
