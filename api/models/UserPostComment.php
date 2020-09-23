<?php

namespace app\models;

use Jdsteam\Sapawarga\Models\Concerns\HasActiveStatus;
use Jdsteam\Sapawarga\Models\Concerns\HasComment;
use Jdsteam\Sapawarga\Models\Contracts\ActiveStatus;
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
    use HasComment;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_post_comments';
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
        $rules = [
            ['user_post_id', 'required'],
        ];

        return array_merge(
            $rules,
            $this->rulesComment()
        );
    }

    public function fields()
    {
        $fields = [
            'user_post_id',
        ];

        return array_merge(
            $fields,
            $this->fieldsComment()
        );
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
        if ($userIdPost != $userIdComment) {
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
}
