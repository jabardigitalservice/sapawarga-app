<?php

namespace app\models;

use app\validator\InputCleanValidator;
use Jdsteam\Sapawarga\Models\Contracts\ActiveStatus;
use Jdsteam\Sapawarga\Models\Concerns\HasActiveStatus;
use Jdsteam\Sapawarga\Models\Concerns\HasCategory;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "news_important".
 *
 * @property int $id
 * @property string $title
 * @property int $category_id
 * @property string $content
 * @property string $image_path
 * @property string $source_url
 * @property string $status
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */

class NewsImportant extends ActiveRecord implements ActiveStatus
{
    use HasActiveStatus, HasCategory;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'news_important';
    }

    public function getAttachments()
    {
        return $this->hasMany(NewsImportantAttachment::className(), ['news_important_id' => 'id']);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['title', 'status', 'category_id', 'content'],'required'],
            ['title', 'string', 'max' => 100],
            ['title', 'string', 'min' => 10],
            [['title', 'content'], InputCleanValidator::class],
            [['title', 'source_url', 'category_id', 'content', 'image_path'], 'trim'],
            [['title', 'source_url', 'category_id', 'content', 'image_path'], 'safe'],

            ['source_url', 'url'],

            ['status', 'integer'],
            ['status', 'in', 'range' => [-1, 0, 10]],
        ];
    }

    public function fields()
    {
        $publicBaseUrl = Yii::$app->params['storagePublicBaseUrl'];

        $fields = [
            'id',
            'title',
            'category_id',
            'category' => 'CategoryField',
            'content',
            'image_path',
            'image_path_url' => function () use ($publicBaseUrl) {
                return "{$publicBaseUrl}/{$this->image_path}";
            },
            'source_url',
            'status',
            'attachments' => function () use ($publicBaseUrl) {
                if ($this->attachments) {
                    foreach ($this->attachments as $key => $value) {
                        $attachments[$key]['id'] = $value->id;
                        $attachments[$key]['file_path'] = $value->file_path;
                        $attachments[$key]['file_url'] = $publicBaseUrl . '/' . $value->file_path;
                    }

                    return $attachments;
                }
            },
            'status_label' => 'StatusLabel',
            'created_at',
            'updated_at',
            'created_by',
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
            'title' => 'Judul',
            'category_id' => 'Kategori',
            'content' => 'Konten',
            'image_path' => 'Gambar',
            'source_url' => 'URL Sumber',
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
