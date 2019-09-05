<?php

namespace app\models;

use Jdsteam\Sapawarga\Models\Contracts\ActiveStatus;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "releases".
 *
 * @property int $id
 * @property string $version
 * @property boolean $force_update
 * @property int $created_by
 * @property int $updated_by
 * @property int $created_at
 * @property int $updated_at
 */
class Release extends ActiveRecord implements ActiveStatus
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'releases';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['version', 'force_update'], 'required'],
            ['version', 'string'],
            ['version', 'trim'],
            ['version', 'unique'],
            [
                'version',
                'match',
                'pattern' => '/^(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)$/'
            ],
            ['force_update', 'boolean'],
        ];
    }

    public function fields()
    {
        $fields = [
            'id',
            'version',
            'force_update',
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
            'version' => 'Versi',
            'force_update' => 'Force Update',
        ];
    }

    /** @inheritdoc */
    public function behaviors()
    {
        return [
            [
                'class'              => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value'              => time(),
            ],
            BlameableBehavior::class,
        ];
    }
}
