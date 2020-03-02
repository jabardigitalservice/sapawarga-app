<?php

namespace app\models;

use Jdsteam\Sapawarga\Models\Concerns\HasActiveStatus;
use Jdsteam\Sapawarga\Models\Contracts\ActiveStatus;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use app\components\ModelHelper;

/**
 * This is the model class for table "news_important_comments".
 *
 * @property int $id
 * @property int $news_important_id
 * @property string $text
 * @property int $status
 * @property int $created_by
 * @property int $updated_by
 * @property int $created_at
 * @property int $updated_by
 */
class NewsImportantComment extends ActiveRecord implements ActiveStatus
{
    use HasActiveStatus;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'news_important_comments';
    }

    public function getAuthor()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    public function getNewsImportant()
    {
        return $this->hasOne(NewsImportant::class, ['id' => 'news_important_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['text', 'string', 'max' => 500],

            [['text'], 'trim'],
            [['text'], 'safe'],

            [['news_important_id', 'text', 'status'], 'required' ],

            ['status', 'integer'],
            ['status', 'in', 'range' => [-1, 0, 10]],
        ];
    }

    public function fields()
    {
        $fields = [
            'id',
            'news_important_id',
            'text',
            'user' => 'AuthorField',
            'created_at',
            'updated_at',
            'created_by',
            'updated_by',
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
            'text' => 'Komentar',
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

    protected function getAuthorField()
    {
        $publicBaseUrl = Yii::$app->params['storagePublicBaseUrl'];

        return [
            'id' => $this->author->id,
            'name' => $this->author->name,
            'photo_url_full' => $this->author->photo_url ? "$publicBaseUrl/{$this->author->photo_url}" : null,
            'role_label' => $this->author->getRoleName(),
            'kabkota' => isset($this->author->kabkota->name) ? $this->author->kabkota->name : null,
            'kelurahan' => isset($this->author->kelurahan->name) ? $this->author->kelurahan->name : null,
            'kecamatan' => isset($this->author->kecamatan->name) ? $this->author->kecamatan->name : null,
            'rw' => isset($this->author->rw) ? $this->author->rw : null,
            'role_label' => $this->author->getRoleName(),
        ];
    }
}
