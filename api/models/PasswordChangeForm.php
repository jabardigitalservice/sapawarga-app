<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * Password Change Form
 */
class PasswordChangeForm extends Model
{
    public $id;
    public $password_updated_at;
    public $password;
    public $password_confirmation;
    public $password_old;


    /** @var User */
    private $_user = false;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['password', 'required'],
            ['password', 'string', 'length' => [5, User::MAX_LENGTH]],
            ['password', 'validateOldPassword'],
            ['password', 'validateConfirmPassword'],
        ];
    }

    public function validateOldPassword($attribute, $params)
    {
        if (!empty($this->password_updated_at)) {
            if (empty($this->password_old)) {
                $this->addError($attribute, \Yii::t('app', 'error.password.old.empty'));
            }

            $user = $this->getUserByID();
            if (!$user->validatePassword($this->password_old)) {
                $this->addError($attribute, \Yii::t('app', 'error.password.old.incorrect'));
            }

            if ($user->validatePassword($this->password)) {
                $this->addError($attribute, \Yii::t('app', 'error.password.old.same'));
            }
        }
    }

    public function validateConfirmPassword($attribute, $params)
    {
        if ($this->password != $this->password_confirmation) {
            $this->addError($attribute, \Yii::t('app', 'error.password.confirmation.incorect'));
        }
    }

    /**
     * Signs user up.
     *
     * @return boolean the saved model or null if saving fails
     */
    public function changePassword()
    {
        if ($this->validate()) {
            $this->getUserByID();
            $this->_user->setPassword($this->password);
            $this->_user->touch('password_updated_at');

            if ($this->_user->save(false)) {
                return true;
            } else {
                $this->addError('generic', Yii::t('app', 'The system could not update the information.'));
            }
        }
        return false;
    }

    /**
     * Finds user by [[id]]
     *
     * @return User|null
     */
    public function getUserByID()
    {
        if ($this->_user === false) {
            $this->_user = User::findOne($this->id);
        }

        return $this->_user;
    }
}
