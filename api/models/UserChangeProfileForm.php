<?php

namespace app\models;

use Jdsteam\Sapawarga\Jobs\EmailJob;
use Yii;
use yii\base\Model;

/**
 * User Edit form
 */
class UserChangeProfileForm extends Model
{
    public $id;
    public $name;
    public $email;
    public $phone;
    public $address;
    /** @var User */
    private $_user = false;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'email', 'phone', 'address'], 'required'],
            [['name', 'email', 'phone', 'address'], 'trim'],
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
            // [['name', 'phone', 'address'], 'default'],
            [['name', 'address'], 'string', 'max' => User::MAX_LENGTH],
            ['phone', 'string', 'length' => [3, 13]],
        ];
    }

    /**
     * Signs user up.
     *
     * @return boolean the saved model or null if saving fails
     */
    public function changeProfile()
    {
        if ($this->validate()) {
            $this->getUserByID();

            if ($this->_user->email != $this->email) {
                $this->_user->unconfirmed_email = $this->email;
                $this->_user->email = $this->email;
                $this->_user->confirmed_at = Yii::$app->formatter->asTimestamp(date('Y-m-d H:i:s'));
                $this->_user->generateAuthKey();
            }

            // Set all the other fields
            $excluded_attributes = ['password'];
            $attribute_names = $this->attributes();
            $attribute_names = array_diff($attribute_names, $excluded_attributes);
            foreach ($attribute_names as $name) {
                $this->_user[$name] = $this[$name];
            }

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

    public function sendConfirmationEmail()
    {
        Yii::$app->queue->push(new EmailJob([
            'user' => $this->_user,
            'email' => $this->email,
        ]));
    }
}
