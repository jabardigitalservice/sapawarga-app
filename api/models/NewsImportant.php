<?php

namespace app\models;

use app\components\ModelHelper;
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
 * @property int $kabkota_id
 * @property string $status
 * @property int $created_by
 * @property int $created_at
 * @property int $updated_by
 * @property int $updated_at
 */

class NewsImportant extends ActiveRecord implements ActiveStatus
{
    use HasActiveStatus, HasCategory;

    const CATEGORY_TYPE = 'news_important';
    const STATUS_PUBLISHED = 10;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'news_important';
    }

    public function getKabkota()
    {
        return $this->hasOne(Area::className(), ['id' => 'kabkota_id']);
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
            ['title', InputCleanValidator::class],
            [['title', 'source_url', 'category_id', 'content', 'image_path'], 'trim'],
            [['title', 'source_url', 'category_id', 'content', 'image_path'], 'safe'],

            ['source_url', 'url'],

            ['kabkota_id', 'integer'],

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
                if (!empty($this->image_path)) {
                    return "{$publicBaseUrl}/{$this->image_path}";
                }
            },
            'source_url',
            'kabkota_id',
            'kabkota'      => function () {
                if (empty($this->kabkota)) {
                    return null;
                }
                return [
                    'id'   => $this->kabkota->id,
                    'name' => $this->kabkota->name,
                ];
            },
            'status',
            'attachments' => function () use ($publicBaseUrl) {
                $attachments = [];
                if ($this->attachments) {
                    foreach ($this->attachments as $key => $value) {
                        $attachments[$key]['id'] = $value->id;
                        $attachments[$key]['name'] = $this->getTitleFile($value->file_path);
                        $attachments[$key]['file_path'] = $value->file_path;
                        $attachments[$key]['file_url'] = $publicBaseUrl . '/' . $value->file_path;
                    }
                }
                return $attachments;
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
            'content' => 'Deskripsi',
            'image_path' => 'Gambar',
            'source_url' => 'Tautan',
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

    public function getTitleFile($filePath)
    {
        $explode = explode('/', $filePath);
        $fileName = !empty($explode[1]) ? $explode[1] : $filePath;

        return $fileName;
    }

    /** @inheritdoc */
    public function afterSave($insert, $changedAttributes)
    {
        $isSendNotification = ModelHelper::isSendNotification($insert, $changedAttributes, $this);

        if ($isSendNotification) {
            $categoryName = Notification::CATEGORY_LABEL_NEWS_IMPORTANT;
            $payload = [
                'categoryName'  => $categoryName,
                'title'         => "Info {$this->category->name}: {$this->title}",
                'description'   => null,
                'target'        => [
                    'kabkota_id'    => $this->kabkota_id,
                ],
                'meta'          => [
                    'target'    => 'news-important',
                    'id'        => $this->id,
                ],
            ];

            ModelHelper::sendNewContentNotification($payload);
        }

        return parent::afterSave($insert, $changedAttributes);
    }
}
