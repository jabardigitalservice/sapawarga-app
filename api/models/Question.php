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
 * @property string $question
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

    public function getComment()
    {
        return $this->hasMany(QuestionComment::class, ['id' => 'question_id']);
    }

    public function getAuthor()
    {
        return $this->hasOne(User::class, ['id' => 'created_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['question', 'string', 'max' => 255],

            [['question'], 'trim'],
            [['question'], 'safe'],
            [['question'],'required'],

            ['status', 'integer'],
            ['status', 'in', 'range' => [-1, 0, 10]],
        ];
    }

    public function fields()
    {
        $fields = [
            'id',
            'question',
            'total_comment',
            'status',
            'status_label' => 'StatusLabel',
            'created_at',
            'updated_at',
            'created_by',
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
            'question' => 'Pertanyaan',
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
        ];
    }
}
