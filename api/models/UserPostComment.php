<?php

namespace app\models;

use Jdsteam\Sapawarga\Models\Concerns\HasActiveStatus;
use Jdsteam\Sapawarga\Models\Contracts\ActiveStatus;
use Yii;
use yii\behaviors\AttributeTypecastBehavior;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use app\components\ModelHelper;

/**
 * This is the model class for table "user_post_comments".
 *
 * @property int $id
 * @property int $user_post_id
 * @property string $text
 * @property int $status
 * @property int $created_by
 * @property int $updated_by
 * @property int $created_at
 * @property int $updated_by
 */
class UserPostComment extends ActiveRecord implements ActiveStatus
{
    use HasActiveStatus;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_post_comments';
    }

    public function getAuthor()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    public function getUserPost()
    {
        return $this->hasOne(UserPost::class, ['id' => 'user_post_id']);
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

            [['user_post_id', 'text', 'status'], 'required' ],

            ['status', 'integer'],
            ['status', 'in', 'range' => [-1, 0, 10]],
        ];
    }

    public function fields()
    {
        $fields = [
            'id',
            'user_post_id',
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
        ];
    }

    public function afterSave($insert, $changedAttributes)
    {
        if ($insert) {
            // Save the last comment id and update comments_count
            $commentsCount = UserPostComment::find()
                ->where(['user_post_id' => $this->user_post_id])
                ->andWhere(['status' => UserPostComment::STATUS_ACTIVE])
                ->count();

            $this->userPost->last_user_post_comment_id = $this->id;
            $this->userPost->comments_count = $commentsCount;
            $this->userPost->save(false);

            // Send push notif
            if (!YII_ENV_TEST) {
                $this->sendNotification($this->userPost->created_by, $this->created_by);
            }
        }
        return parent::afterSave($insert, $changedAttributes);
    }

    protected function sendNotification($userIdPost, $userIdComment)
    {
        $userIdPost = User::findIdentity($userIdPost);
        $userIdComment = User::findIdentity($userIdComment);

        $payload = [
            'categoryName'  => Notification::CATEGORY_LABEL_USER_POST,
            'title'         => "{$userIdComment->name} telah memberikan komentar pada kegiatan RW Anda",
            'description'   => null,
            'target'        => [
                'kabkota_id'    => $userIdPost->kabkota_id,
                'kec_id'        => $userIdPost->kec_id,
                'kel_id'        => $userIdPost->kel_id,
                'rw'            => $userIdPost->rw,
                'push_token'    => $userIdPost->push_token,
            ],
            'meta'          => [
                'target'    => 'user-post',
                'id'        => $this->user_post_id,
            ],
        ];

        ModelHelper::sendNewContentNotification($payload);
    }
}
