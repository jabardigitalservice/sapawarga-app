<?php

namespace app\models;

use Jdsteam\Sapawarga\Models\Concerns\HasActiveStatus;
use Jdsteam\Sapawarga\Models\Contracts\ActiveStatus;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "news_channels".
 *
 * @property int $id
 * @property string $name
 * @property string $website
 * @property string $icon_url
 * @property array $meta
 * @property int $status
 */
class NewsChannel extends ActiveRecord implements ActiveStatus
{
    use HasActiveStatus;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'news_channels';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['name', 'string', 'min' => 5],
            ['name', 'string', 'max' => 25],
            [['name', 'website'], 'unique'],
            [['name', 'website', 'icon_url'], 'trim'],
            [['name', 'website', 'icon_url'], 'safe'],

            [['name', 'status'], 'required'],

            ['status', 'integer'],
        ];
    }

    public function fields()
    {
        $fields = [
            'id',
            'name',
            'icon_url',
            'website',
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
            'id'       => 'ID',
            'website'  => 'Website',
            'icon_url' => 'Icon URL',
            'name'     => 'Nama',
            'meta'     => 'Meta',
            'status'   => 'Status',
        ];
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
            ],
            BlameableBehavior::class,
        ];
    }
}
