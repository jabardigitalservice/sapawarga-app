<?php

namespace app\models;

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
        return $this->pushQueue();
    }

    protected function resetUsernamePassword($user)
    {
        // Generate Username and/or password but easier to read and remember
        $user->username = 'staffrw' . $this->phone  . rand(100, 999);
        $this->message = 'Sapawarga - WASPADA PENIPUAN! JANGAN MEMBERITAHUKAN ID PENGGUNA DAN KATA SANDI ANDA KE SIAPA PUN termasuk pihak Sapawarga. Berikut ID Pengguna Anda: ' . $user->username;
        if ($this->reset_type == User::RESET_USERNAME_AND_PASSWORD) {
            // Generate Pass
            $newPassword = substr(str_shuffle('staffrw' . $this->phone  . rand(100, 999)), 0, 8);
            $user->setPassword($newPassword);
            $this->message .= ', dan Kata Sandi Anda: ' . $newPassword;
        }

        return $user;
    }

    protected function getClient()
    {
        return new SqsClient([
            'credentials' => [
                'key' => Yii::$app->sqsQueue->key,
                'secret' => Yii::$app->sqsQueue->secret,
            ],
            'region' => Yii::$app->sqsQueue->region,
            'version' => Yii::$app->sqsQueue->version,
        ]);
    }

    protected function pushQueue()
    {
        $messageRequest = [
            'QueueUrl'          => Yii::$app->sqsQueue->url,
            'DelaySeconds'      => 10,
            'MessageAttributes' => [
                'PhoneNumber'   => [
                    'DataType'    => 'String',
                    'StringValue' => $this->cleanPhoneNumber($this->phone)
                ]
            ],
            'MessageBody' => $this->message,
        ];
        
        try {
            return $this->getClient()->sendMessage($messageRequest);
        } catch (AwsException $e) {
            // output error message if fails
            error_log($e->getMessage());
        }
    }

    protected function cleanPhoneNumber($phoneNumber)
    {
        return preg_replace('/^0{1}/', '62', $phoneNumber);
    }
}
