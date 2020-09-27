<?php

namespace app\models;

use app\components\ModelHelper;
use Jdsteam\Sapawarga\Models\Concerns\HasActiveStatus;
use Jdsteam\Sapawarga\Models\Contracts\ActiveStatus;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use app\validator\IsArrayValidator;

/**
 * This is the model class for table "post".
 *
 * @property int $id
 * @property string $text
 * @property string $images
 * @property int $status
 * @property int $created_by
 * @property int $updated_by
 * @property int $created_at
 * @property int $updated_by
 */
class UserPost extends ActiveRecord implements ActiveStatus
{
    use HasActiveStatus;

    public const CATEGORY_TYPE = 'user_post';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_posts';
    }

    public function getAuthor()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    public function getComments()
    {
        return $this->hasMany(UserPostComment::class, ['user_post_id' => 'id']);
    }

    public function getLastComment()
    {
        return UserPostComment::findOne($this->last_user_post_comment_id);
    }

    public function getIsUserLiked()
    {
        return ModelHelper::getIsUserLiked($this->id, Like::TYPE_USER_POST);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['text', 'string', 'max' => 1000],
            ['text', 'string', 'min' => 10],

            [['text', 'tags'], 'trim'],
            [['text', 'tags'], 'safe'],
            [['text', 'status', 'image_path', 'images'], 'required'],

            ['images', IsArrayValidator::class],

            [['status', 'last_user_post_comment_id'], 'integer'],
            ['status', 'in', 'range' => [-1, 0, 10]],
        ];
    }

    public function fields()
    {
        $fields = [
            'id',
            'text',
            'tags',
            'image_path_full' => function () {
                $publicBaseUrl = Yii::$app->params['storagePublicBaseUrl'];
                return "{$publicBaseUrl}/{$this->image_path}";
            },
            'images' => 'imagesField',
            'likes_count',
            'comments_count',
            'last_user_post_comment_id',
            'last_comment' => 'lastComment',
            'status',
            'status_label' => 'StatusLabel',
            'created_at',
            'updated_at',
            'created_by',
            'is_liked' => 'IsUserLiked',
            'user' => 'AuthorField',
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
            'text' => 'Deskripsi',
            'images' => 'Photo',
            'image_path' => 'Photo',
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

    protected function getImagesField()
    {
        if ($this->images === null) {
            return null;
        }

        return array_map(function ($item) {
            $publicBaseUrl = Yii::$app->params['storagePublicBaseUrl'];
            return [
                'url'  => "{$publicBaseUrl}/{$item['path']}",
            ];
        }, $this->images);
    }
}
