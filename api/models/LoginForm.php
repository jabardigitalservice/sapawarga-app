<?php

namespace app\models;

use app\components\LogHelper;
use Monolog\Logger;
use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 *
 * @property User|null $user This property is read-only.
 *
 */
class LoginForm extends Model
{
    // Constants for Scenario names
    const SCENARIO_LOGIN = 'login';

    const LOGIN_DURATION_USER = 3600 * 24 * 30 * 6; // 6 months
    const LOGIN_DURATION_STAFF = 3600 * 24 * 30; // 1 month
    const USER_ROLES = [
        User::ROLE_STAFF_RW,
        User::ROLE_TRAINER,
        User::ROLE_USER,
    ];

    public $username;
    public $password;
    public $push_token;
    public $roles = [];
    public $rememberMe = true;
    /** @var User */
    private $_user = false;

    /** @inheritdoc */
    public function attributeLabels()
    {
        return [
            'username' => Yii::t('app', \Yii::t('app', 'app.username')),
            'password' => Yii::t('app', \Yii::t('app', 'app.password')),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_LOGIN] = ['username', 'password', 'push_token'];
        return $scenarios;
    }

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['username', 'password'], 'required'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
            ['push_token', 'safe'],
        ];
    }

    /**
     * Validates user by status.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validateUser($user)
    {
        if ($user->status === User::STATUS_DISABLED || $user->status === User::STATUS_PENDING) {
            $this->addError('status', \Yii::t('app', 'error.login.inactive'));
        }
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUserByUsername();

            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, \Yii::t('app', 'error.login.incorrect'));
            } else {
                $this->validateUser($user);
            }
        }
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUserByUsername()
    {
        // Roles must be set to get an user
        if (empty($this->roles)) {
            return null;
        }
        if ($this->_user === false) {
            $this->_user = User::findByUsernameWithRoles($this->username, $this->roles);
        }

        return $this->_user;
    }

    /**
     * Logs in a user using the provided username and password.
     * @return bool whether the user is logged in successfully
     */
    public function login()
    {
        /**
         * @var Logger $logger
         */
        $monologComponent = Yii::$app->monolog;
        $logger = $monologComponent->getLogger('main');
        $user = $this->getUserByUsername();

        if ($this->validate()) {
            if (in_array($user->role, LoginForm::USER_ROLES)) {
                $expirationDuration = LoginForm::LOGIN_DURATION_USER;
            } else {
                $expirationDuration = LoginForm::LOGIN_DURATION_STAFF;
            }

            $login = Yii::$app->user->login($this->getUserByUsername(), $this->rememberMe ? $expirationDuration : 0);
            LogHelper::logEventByUser('LOGIN_SUCCESS');
            return $login;
        }

        if ($user) {
            LogHelper::logEventByUser('LOGIN_FAILED_INVALID_PASSWORD', $user);
        } else {
            $logger->info('LOGIN_FAILED_UNKNOWN_USER', ['username' => $this->username]);
        }

        return false;
    }

    /**
     * Return User object
     *
     * @return User
     */
    public function getUser()
    {
        return $this->_user;
    }
}
