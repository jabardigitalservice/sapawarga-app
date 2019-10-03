<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * Password reset request form
 */
class PasswordResetRequestForm extends Model
{
    public $email;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'email'],
            [
                'email',
                'exist',
                'targetClass' => '\app\models\User',
                'filter' => ['status' => User::STATUS_ACTIVE],
                'message' => Yii::t('app', 'There is no user with this email address.')
            ],
        ];
    }

    /**
     * Sends an email with a link, for resetting the password.
     *
     * @return bool whether the email was send
     */
    public function sendPasswordResetEmail()
    {
        /* @var $user User */
        $user = User::findOne([
            'status' => User::STATUS_ACTIVE,
            'email' => $this->email,
        ]);

        if (!$user) {
            return false;
        }

        if (!User::isPasswordResetTokenValid($user->password_reset_token)) {
            $user->generatePasswordResetToken();
            if (!$user->save(false)) {
                return false;
            }
        }

        $resetURL = Yii::$app->params['frontendURL'] . '/#/reset-password?token=' . $user->password_reset_token;

        $fromEmail = Yii::$app->params['adminEmail'];
        $fromName  = Yii::$app->params['adminEmailName'];

        return Yii::$app
            ->mailer
            ->compose(
                ['html' => 'password-reset-token-html'],
                [
                    'user' => $user,
                    'appName' => \Yii::$app->name,
                    'resetURL' => $resetURL,
                ]
            )
            ->setFrom([$fromEmail => $fromName])
            ->setTo($this->email)
            ->setSubject('Permintaan Reset Password untuk akun Sapawarga ')
            ->send();
    }
}
