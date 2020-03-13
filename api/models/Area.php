<?php

namespace app\models;

use Jdsteam\Sapawarga\Models\Concerns\HasActiveStatus;
use Jdsteam\Sapawarga\Models\Contracts\ActiveStatus;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "areas".
 *
 * @property int $id
 * @property int $depth
 * @property int $parent_id
 * @property string $name
 * @property string $code_bps
 * @property string $code_kemendagri
 * @property bool $status
 */
class Area extends \yii\db\ActiveRecord implements ActiveStatus
{
    use HasActiveStatus;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'areas';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['name', 'string', 'max' => 64],
            [['name', 'code_bps', 'code_kemendagri', 'latitude', 'longitude', 'meta'], 'trim'],
            [['parent_id', 'depth', 'name', 'code_bps', 'code_kemendagri', 'status'], 'required'],
            [['parent_id', 'depth'], 'integer'],
            [['status'], 'boolean'],
        ];
    }

    public function fields()
    {
        $fields = [
            'id',
            'parent_id',
            'depth',
            'name',
            'code_bps',
            'code_kemendagri',
            'latitude',
            'longitude',
            'meta',
            'status',
            'status_label' => 'StatusLabel',
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
            'parent_id' => 'Parent ID',
            'name' => 'Nama',
        ];
    }

    /** @inheritdoc */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => time()
            ]
        ];
    }
}
