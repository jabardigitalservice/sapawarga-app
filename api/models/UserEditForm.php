<?php

namespace app\models;

use app\validator\MinimumAgeValidator;
use app\validator\DatePastValidator;
use Illuminate\Support\Arr;
use Yii;
use yii\base\Model;

/**
 * User Edit form
 */
class UserEditForm extends Model
{
    public $id;
    public $username;
    public $password;
    public $email;
    public $name;
    public $phone;
    public $address;
    public $rt;
    public $rw;
    public $kel_id;
    public $kec_id;
    public $kabkota_id;
    public $lat;
    public $lon;
    public $photo_url;
    public $facebook;
    public $twitter;
    public $instagram;
    public $birth_date;
    public $job_type_id;
    public $education_level_id;

    /** @var User */
    private $_user = false;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                'id',
                'exist',
                'targetClass' => '\app\models\User',
                'filter' => [
                    'and',
                    ['status' => User::STATUS_ACTIVE],
                    'confirmed_at IS NOT NULL',
                    'blocked_at IS NULL'
                ],
                'message' => 'The ID is not valid.'
            ],

            ['username', 'trim'],
            ['username', 'string', 'length' => [4, 255]],
            [
                'username',
                'match',
                'pattern' => '/^[a-z0-9_.]{4,255}$/',
                'message' => Yii::t('app', 'error.username.pattern')
            ],
            [
                'username',
                'unique',
                'targetClass' => '\app\models\User',
                'message' => Yii::t('app', 'error.username.taken'),
                'filter' => function ($query) {
                    $query->andWhere(['!=', 'id', $this->id]);
                }
            ],

            ['email', 'trim'],
            ['email', 'email'],
            ['email', 'string', 'max' => User::MAX_LENGTH],
            [
                'email',
                'unique',
                'targetClass' => '\app\models\User',
                'message' => Yii::t('app', 'error.email.taken'),
                'filter' => function ($query) {
                    $query->andWhere(['!=', 'id', $this->id]);
                }
            ],

            ['password', 'string', 'length' => [5, User::MAX_LENGTH]],
            [['username', 'email', 'name', 'phone', 'address', 'rt', 'rw', 'kel_id', 'kec_id', 'kabkota_id', 'lat', 'lon', 'photo_url', 'facebook', 'twitter', 'instagram'], 'default'],
            [['birth_date', 'education_level_id', 'job_type_id'], 'default'],
            [['name', 'address'], 'string', 'max' => User::MAX_LENGTH],
            ['phone', 'string', 'length' => [3, 13]],

            ['birth_date', 'date', 'format' => 'php:Y-m-d'],
            ['birth_date', DatePastValidator::class],
            ['birth_date', MinimumAgeValidator::class],
        ];
    }

    /**
     * Update own Profile (with allow update partial attributes)
     * POST /v1/user/me
     * POST /v1/staff/me
     *
     * @param  array  $attributes
     * @return boolean the saved model or null if saving fails
     */
    public function save(array $attributes = []): bool
    {
        $this->getUserByID();

        // If password is not null and not empty, then update password
        $newPassword = Arr::get($attributes, 'password');
        if ($newPassword !== null) {
            $this->_user->setPassword($newPassword);
        }

        // Remove password because password must set by hash method (already above)
        Arr::forget($attributes, 'password');

        // Update partial attributes from input
        foreach ($attributes as $attribute => $value) {
            $this->_user->$attribute = $value;
        }

        // Because we only update partial attributes, so don't need to fulfil User() validation
        return $this->_user->save(false);
    }

    /**
     * Finds user by [[id]]
     *
     * @return User|null$attribute
     */
    public function getUserByID()
    {
        if ($this->_user === false) {
            $this->_user = User::findOne($this->id);
        }

        return $this->_user;
    }

    public function sendConfirmationEmail()
    {
        $confirmURL = \Yii::$app->params['frontendURL'] . '#/confirm?id=' . $this->_user->id . '&auth_key=' . $this->_user->auth_key;

        $email = \Yii::$app->mailer
            ->compose(
                ['html' => 'email-confirmation-html'],
                [
                    'appName' => \Yii::$app->name,
                    'confirmURL' => $confirmURL,
                ]
            )
            ->setTo($this->email)
            ->setFrom([\Yii::$app->params['supportEmail'] => \Yii::$app->name])
            ->setSubject('Email confirmation')
            ->send();

        return $email;
    }
}
