<?php

namespace app\models;

use app\components\ModelHelper;
use Jdsteam\Sapawarga\Models\Concerns\HasActiveStatus;
use Jdsteam\Sapawarga\Models\Contracts\ActiveStatus;
use Yii;
use yii\behaviors\AttributeTypecastBehavior;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "post".
 *
 * @property int $id
 * @property string $text
 * @property string $image_path
 * @property int $status
 * @property int $created_by
 * @property int $updated_by
 * @property int $created_at
 * @property int $updated_by
 */
class UserPost extends ActiveRecord implements ActiveStatus
{
    use HasActiveStatus;

    public $likes_count;

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

    public function getLikes()
    {
        return $this->hasMany(Like::class, ['entity_id' => 'id'])
                    ->andOnCondition(['type' => Like::TYPE_USER_POST]);
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

            [['text'], 'trim'],
            [['text'], 'safe'],
            [['text', 'status', 'image_path'],'required'],

            [['status', 'last_user_post_comment_id'], 'integer'],
            ['status', 'in', 'range' => [-1, 0, 10]],
        ];
    }

    public function fields()
    {
        $fields = [
            'id',
            'text',
            'image_path_full' => function () {
                $publicBaseUrl = Yii::$app->params['storagePublicBaseUrl'];
                return "{$publicBaseUrl}/{$this->image_path}";
            },
            'likes_count',
            'comments_count' => 'CommentsCount',
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
            'typecast' => [
                'class' => AttributeTypecastBehavior::class,
                'attributeTypes' => [
                    'likes_count' => AttributeTypecastBehavior::TYPE_INTEGER,
                ],
                'typecastAfterValidate' => false,
                'typecastBeforeSave' => false,
                'typecastAfterFind' => true,
            ],
            BlameableBehavior::class,
        ];
    }

    protected function getCommentsCount()
    {
        return (int)$this->getComments()->count();
    }

    protected function getAuthorField()
    {
        $publicBaseUrl = Yii::$app->params['storagePublicBaseUrl'];

        return [
            'id' => $this->author->id,
            'name' => $this->author->name,
            'photo_url_full' => $this->author->photo_url ? "$publicBaseUrl/{$this->author->photo_url}" : null,
            'role_label' => $this->author->getRoleName(),
            'kabkota' => $this->author->kabkota->name,
            'kelurahan' => $this->author->kelurahan->name,
            'kecamatan' => $this->author->kecamatan->name,
        ];
    }
}
