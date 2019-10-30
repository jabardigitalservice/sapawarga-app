<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * User Import
 */
class UserImport extends Model
{
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
    public $role;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            ['password', 'string', 'length' => [5, User::MAX_LENGTH]],
            [['name', 'address'], 'string', 'max' => User::MAX_LENGTH],
            ['phone', 'string', 'length' => [3, 13]],

            [['role', 'kabkota_id', 'kec_id', 'kel_id', 'rw', 'rt'], 'default'],

            ['kabkota_id', 'required', 'when' => function ($model) {
                return $model->role <= User::ROLE_STAFF_KABKOTA;
            }],
            ['kec_id', 'required', 'when' => function ($model) {
                return $model->role <= User::ROLE_STAFF_KEC;
            }],
            ['kel_id', 'required', 'when' => function ($model) {
                return $model->role <= User::ROLE_STAFF_KEL;
            }],
            ['rw', 'required', 'when' => function ($model) {
                return $model->role <= User::ROLE_STAFF_RW;
            }],
        ];

        return array_merge($rules, $this->rulesUsername(), $this->rulesEmail());
    }

    protected function rulesUsername()
    {
        return [
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
                'targetClass' => User::class,
                'message' => Yii::t('app', 'error.username.taken'),
            ],
        ];
    }

    protected function rulesEmail()
    {
        return [
            ['email', 'trim'],
            ['email', 'email'],
            ['email', 'string', 'max' => User::MAX_LENGTH],
            [
                'email',
                'unique',
                'targetClass' => User::class,
                'message' => Yii::t('app', 'error.email.taken'),
            ],
        ];
    }
}
