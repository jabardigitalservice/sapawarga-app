<?php

namespace app\models;

use app\components\WhatsappTrait;
use Yii;
use yii\base\Model;

/**
 * User Edit form
 */
class UserChangeUsernameForm extends Model
{
    use WhatsappTrait;

    public $id;
    public $username;
    public $phone;
    public $is_username_updated;
    private $_user = false;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'phone'], 'required'],
            [['username', 'phone'], 'trim'],
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
            ['phone', 'string', 'length' => [3, 15]]
        ];
    }

    /** @inheritdoc */
    public function attributeLabels()
    {
        return [
            'username' => Yii::t('app', 'app.username'),
            'phone' => Yii::t('app', 'app.phone')
        ];
    }

    /**
     * Signs user up.
     *
     * @return boolean the saved model or null if saving fails
     */
    public function changeUsername()
    {
        if ($this->validate()) {
            $this->getUserByID();

            // Set all the other fields
            $attribute_names = $this->attributes();
            foreach ($attribute_names as $name) {
                $this->_user[$name] = $this[$name];
            }

            if ($this->_user->save(false)) {
                $this->_user->touch('profile_updated_at');
                $this->_user->touch('username_updated_at');
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

    public function sendWhatsappInfo()
    {
        $message = \Yii::t('app', 'message.info_change_username_and_password_success');
        return $this->pushQueue($this->phone, $message);
    }
}
