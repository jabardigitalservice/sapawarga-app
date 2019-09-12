<?php

namespace app\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "news_featured".
 *
 * @property int $id
 * @property int $news_id
 * @property int $kabkota_id
 * @property int $seq
 */
class NewsFeatured extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'news_featured';
    }

    public function getKabkota()
    {
        return $this->hasOne(Area::class, ['id' => 'kabkota_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['news_id', 'required'],
            ['news_id', 'integer'],
            ['seq', 'required'],
            ['seq', 'integer'],
            ['kabkota_id', 'integer'],
        ];
    }

    public function fields()
    {
        return parent::fields();
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'          => 'ID',
            'news_id'     => 'Berita',
            'seq'         => 'Sequence',
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
