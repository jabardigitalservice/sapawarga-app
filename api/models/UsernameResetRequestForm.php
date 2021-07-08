<?php

namespace app\models;

use app\components\WhatsappTrait;
use Aws\Exception\AwsException;
use Aws\Sqs\SqsClient;
use Jdsteam\Sapawarga\Jobs\WhatsappJob;
use Yii;
use yii\base\Model;

/**
 * Username and/or password reset request form
 */
class UsernameResetRequestForm extends Model
{
    use WhatsappTrait;

    public $phone;
    public $reset_type;
    public $message;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['phone', 'reset_type'], 'trim'],
            [['phone', 'reset_type'], 'required'],
            [
                'phone',
                'exist',
                'targetClass' => '\app\models\User',
                'filter' => ['status' => User::STATUS_ACTIVE],
                'message' => Yii::t('app', 'There is no user with this phone number.')
            ],
            ['reset_type', 'integer'],
            ['reset_type', 'in', 'range' => [User::RESET_USERNAME_ONLY, User::RESET_USERNAME_AND_PASSWORD]],
        ];
    }

    /**
     * Sends an email with a link, for resetting the password.
     *
     * @return bool whether the email was send
     */
    public function sendUsernameResetWhatsappMessage()
    {
        /* @var $user User */
        $user = User::findOne([
            'status' => User::STATUS_ACTIVE,
            'phone' => $this->phone,
        ]);

        if (!$user) {
            return false;
        }

        $user = $this->resetUsernamePassword($user);

        if (!$user->save(false)) {
            return false;
        }
        return $this->pushQueue($this->phone, $this->message);
    }

    protected function resetUsernamePassword($user)
    {
        // Generate Username and/or password but easier to read and remember
        $user->username = 'user' . substr($this->phone, -4) . rand(100, 999);
        $this->message = \Yii::t('app', 'message.forgot_username_confirmation') . $user->username;
        if ($this->reset_type == User::RESET_USERNAME_AND_PASSWORD) {
            // Generate Pass
            $newPassword = substr(str_shuffle('staffrw' . $this->phone . rand(100, 999)), 0, 8);
            $user->setPassword($newPassword);
            $this->message .= \Yii::t('app', 'message.and_your_password_is') . $newPassword;
        }

        return $user;
    }
}
