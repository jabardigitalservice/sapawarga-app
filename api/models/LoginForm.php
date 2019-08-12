<?php

namespace app\models;

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
            ['username', 'validateUser'],
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
    public function validateUser($attribute, $params)
    {
        $user = User::findOne(['username' => $this->username]);
        if ($user) {
            if ($user->status === User::STATUS_DISABLED || $user->status === User::STATUS_PENDING) {
                $this->addError($attribute, \Yii::t('app', 'error.login.inactive'));
            }
        } else {
            $this->addError($attribute, \Yii::t('app', 'error.login.incorrect'));
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
        if ($this->validate()) {
            return Yii::$app->user->login($this->getUserByUsername(), $this->rememberMe ? 3600 * 24 * 30 : 0);
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
