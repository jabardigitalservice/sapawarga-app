<?php

namespace app\models;

use app\components\ModelHelper;
use Jdsteam\Sapawarga\Models\Concerns\HasUserLiked;
use Jdsteam\Sapawarga\Models\Concerns\HasActiveStatus;
use Jdsteam\Sapawarga\Models\Contracts\ActiveStatus;
use Yii;
use yii\behaviors\AttributeTypecastBehavior;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "question".
 *
 * @property int $id
 * @property string $text
 * @property bool $is_flagged
 * @property int $status
 * @property int $created_by
 * @property int $updated_by
 * @property int $created_at
 * @property int $updated_by
 */
class Question extends ActiveRecord implements ActiveStatus
{
    use HasActiveStatus, HasUserLiked;

    public $likes_count;

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
        return $this->hasMany(Like::class, ['entity_id' => 'id'])
                    ->andOnCondition(['type' => Like::TYPE_QUESTION]);
    }

    public function getLastAnswer()
    {
        return QuestionComment::findOne($this->answer_id);
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
            [['text', 'status'],'required'],

            [['status', 'answer_id', 'is_flagged'], 'integer'],
            ['status', 'in', 'range' => [-1, 0, 10]],
            ['is_flagged', 'in', 'range' => [0, 1]],
        ];
    }

    public function fields()
    {
        $fields = [
            'id',
            'text',
            'likes_count',
            'comments_count' => 'CommentsCount',
            'answer_id',
            'answer' => 'lastAnswer',
            'status',
            'status_label' => 'StatusLabel',
            'created_at',
            'updated_at',
            'created_by',
            'is_flagged',
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
        ];
    }
}
