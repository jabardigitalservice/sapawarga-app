<?php

namespace app\models;

use Jdsteam\Sapawarga\Models\Concerns\HasActiveStatus;
use Jdsteam\Sapawarga\Models\Contracts\ActiveStatus;
use Yii;
use yii\behaviors\AttributeTypecastBehavior;
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

    /** @var string */
    public $user_name;
    /** @var string */
    public $user_photo_url;
    /** @var string */
    public $user_role_id;

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
        $this->getUserField();

        $fields = [
            'id',
            'question_id',
            'text',
            'user_name',
            'user_photo_url',
            'user_role_id',
            'is_flagged',
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
                'class'              => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value'              => time(),
            ],
            'typecast' => [
                'class' => AttributeTypecastBehavior::class,
                'attributeTypes' => [
                    'is_flagged' => AttributeTypecastBehavior::TYPE_BOOLEAN,
                ],
                'typecastAfterValidate' => false,
                'typecastBeforeSave' => false,
                'typecastAfterFind' => true,
            ],
            BlameableBehavior::class,
        ];
    }

    protected function getUserField()
    {
        $publicBaseUrl = Yii::$app->params['storagePublicBaseUrl'];

        $this->user_name = $this->user->name;
        $this->user_photo_url = $this->user->photo_url ? "$publicBaseUrl/{$this->user->photo_url}" : null;
        $this->user_role_id = array_search($this->user->role, User::ROLE_MAP);
    }
}
