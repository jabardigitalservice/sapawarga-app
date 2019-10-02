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

    public static function primaryKey()
    {
        return ['news_id'];
    }

    public function getNews()
    {
        return $this->hasOne(News::class, ['id' => 'news_id']);
    }

    public function getKabkota()
    {
        return $this->hasOne(Area::class, ['id' => 'kabkota_id']);
    }

    protected function getNewsChannel()
    {
        return $this->news->channel;
    }

    public function getNewsTitle()
    {
        return $this->news->title;
    }

    public function getNewsContent()
    {
        return $this->news->content;
    }

    public function getNewsCoverPathUrl()
    {
        $publicBaseUrl = Yii::$app->params['storagePublicBaseUrl'];

        return "{$publicBaseUrl}/{$this->news->cover_path}";
    }

    public function getNewsSourceDate()
    {
        return $this->news->source_date;
    }

    public function getNewsSourceUrl()
    {
        return $this->news->source_url;
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
        return [
            'id' => function () {
                return $this->news->id;
            },
            'title' => 'NewsTitle',
            'content' => 'NewsContent',
            'cover_path_url' => 'NewsCoverPathUrl',
            'source_date' => 'NewsSourceDate',
            'source_url' => 'NewsSourceUrl',
            'channel' => 'NewsChannel',
            'seq',
        ];
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
