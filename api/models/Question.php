<?php

namespace app\models;

use app\components\ModelHelper;
use Jdsteam\Sapawarga\Models\Concerns\HasActiveStatus;
use Jdsteam\Sapawarga\Models\Contracts\ActiveStatus;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "question".
 *
 * @property int $id
 * @property string $text
 * @property int $status
 * @property int $created_by
 * @property int $updated_by
 * @property int $created_at
 * @property int $updated_by
 */
class Question extends ActiveRecord implements ActiveStatus
{
    use HasActiveStatus;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'questions';
    }

    public function getAuthor()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    public function getComments()
    {
        return $this->hasMany(QuestionComment::class, ['question_id' => 'id']);
    }

    public function getLikes()
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])
                    ->viaTable('likes', ['entity_id' => 'id'], function ($query) {
                        $query->andWhere(['type' => Like::TYPE_QUESTION]);
                    });
    }

    public function getIsLiked()
    {
        $isLiked = Like::find()
            ->where(['entity_id' => $this->id])
            ->andWhere(['type' => Like::TYPE_QUESTION])
            ->andWhere(['user_id' => Yii::$app->user->id])
            ->count();

        if ($isLiked > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['text', 'string', 'max' => 255],

            [['text'], 'trim'],
            [['text'], 'safe'],
            [['text'],'required'],

            ['status', 'integer'],
            ['status', 'in', 'range' => [-1, 0, 5, 10]],
        ];
    }

    public function fields()
    {
        $fields = [
            'id',
            'text',
            'likes_count' => 'LikesCount',
            'comments_count' => 'CommentsCount',
            'status',
            'status_label' => 'StatusLabel',
            'created_at',
            'updated_at',
            'created_by',
            'is_liked' => 'isLiked',
            'author' => 'AuthorField',
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
            'text' => 'Pertanyaan',
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

    protected function getLikesCount()
    {
        return (int)$this->getLikes()->count();
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
            'role_label' => $this->author->getRoleLabel(),
        ];
    }
}
