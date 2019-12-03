<?php

namespace app\models;

use Jdsteam\Sapawarga\Models\Concerns\HasActiveStatus;
use Jdsteam\Sapawarga\Models\Contracts\ActiveStatus;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "question_comments".
 *
 * @property int $id
 * @property int $question_id
 * @property string $text
 * @property boolean $is_flagged
 * @property int $status
 * @property int $created_by
 * @property int $updated_by
 * @property int $created_at
 * @property int $updated_by
 */
class QuestionComment extends ActiveRecord implements ActiveStatus
{
    use HasActiveStatus;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'question_comments';
    }

    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    public function getQuestion()
    {
        return $this->hasOne(Question::class, ['id' => 'question_id']);
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

            [['question_id', 'text', 'status'], 'required' ],

            ['is_flagged', 'boolean', 'trueValue' => true, 'falseValue' => false, 'strict' => true],

            ['status', 'integer'],
            ['status', 'in', 'range' => [-1, 0, 10]],
        ];
    }

    public function fields()
    {
        $fields = [
            'id',
            'question_id',
            'text',
            'is_flagged',
            'created_at',
            'updated_at',
            'created_by',
            'updated_by',
        ];

        $userFields = [
            'user_name' => function() {
                $userFieldsArray = $this->getUserField();
                return $userFieldsArray['name'];
            },
            'user_photo_url' => function() {
                $userFieldsArray = $this->getUserField();
                return $userFieldsArray['photo_url'];
            },
            'user_role_id' => function() {
                $userFieldsArray = $this->getUserField();
                return $userFieldsArray['role_id'];
            },
        ];

        $fields = array_merge($fields, $userFields);

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
                'class'              => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value'              => time(),
            ],
            BlameableBehavior::class,
        ];
    }

    protected function getUserField()
    {
        $publicBaseUrl = Yii::$app->params['storagePublicBaseUrl'];

        return [
            'name' => $this->user->name,
            'photo_url' => $this->user->photo_url ? "$publicBaseUrl/{$this->user->photo_url}" : null,
            'role_id' => array_search($this->user->role, User::ROLE_MAP),
        ];
    }
}
