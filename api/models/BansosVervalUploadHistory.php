<?php

namespace app\models;

use app\validator\InputCleanValidator;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "bansos_verval_upload_histories".
 *
 * @property int $id
 * @property int $status
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */

class BansosVervalUploadHistory extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */

    public $uploadFile;

    public static function tableName()
    {
        return 'bansos_verval_upload_histories';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'verval_type', 'file_path', 'kabkota_code'], 'required'],

            [['uploadFile'], 'file', 'skipOnEmpty' => false, 'extensions' => 'xls, xlsx'],

            [['file_path', 'invalid_file_path', 'verval_type'], 'trim'],

            [['user_id', 'internal_entity_id'], 'integer'],
        ];
    }

    public function fields()
    {
        $publicBaseUrl = Yii::$app->params['storagePublicBaseUrl'];

        $fields = [
            'id',
            'user_id',
            'verval_type',
            'kabkota_code',
            'kec_code',
            'kel_code',
            'original_filename',
            'file_path',
            'file_path_url' => function () use ($publicBaseUrl) {
                return "{$publicBaseUrl}/{$this->file_path}";
            },
            'invalid_file_path',
            'invalid_file_path_url' => function () use ($publicBaseUrl) {
                return "{$publicBaseUrl}/{$this->invalid_file_path}";
            },
            'total_row',
            'successed_row',
            'status',
            'notes',
            'created_by',
            'updated_by',
            'created_at',
            'updated_at',
        ];

        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'status' => 'Status',
        ];
    }

    /** @inheritdoc */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => time(),
            ],
            BlameableBehavior::class,
        ];
    }
}
