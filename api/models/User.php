<?php

namespace app\models;

use Carbon\Carbon;
use Firebase\JWT\JWT;
use Illuminate\Support\Collection;
use Jdsteam\Sapawarga\Models\Concerns\HasArea;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\web\Request as WebRequest;

/**
 * Class User
 *
 * @property integer $id
 * @property string $unique_id
 * @property string $username
 * @property string $auth_key
 * @property integer $access_token_expired_at
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $email
 * @property string $unconfirmed_email
 * @property integer $confirmed_at
 * @property string $registration_ip
 * @property integer $last_login_at
 * @property string $last_login_ip
 * @property integer $blocked_at
 * @property boolean $status
 * @property integer $role
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $name
 * @property string $phone
 * @property string $address
 * @property string $rt
 * @property string $rw
 * @property string $kel_id
 * @property string $kec_id
 * @property string $kabkota_id
 * @property string $lat
 * @property string $lon
 * @property string $photo_url
 * @property string $facebook
 * @property string $twitter
 * @property string $instagram
 * @property string $birth_date
 * @property int $job_type_id
 * @property int $education_level_id
 * @property string $push_token
 * @property string $last_access_at
 * @property string $account_confirmed_at
 * @property string $profile_updated_at
 * @property int $is_username_updated
 * @property int $username_update_popup_at
 *
 * @package app\models
 */
class User extends \yii\db\ActiveRecord implements \yii\web\IdentityInterface
{
    use HasArea;

    public const MAX_LENGTH = 255;

    // Constants for User's role and status
    public const ROLE_USER = 10;
    public const ROLE_TRAINER = 49;
    public const ROLE_STAFF_RW = 50;
    public const ROLE_STAFF_KEL = 60;
    public const ROLE_STAFF_KEC = 70;
    public const ROLE_STAFF_KABKOTA = 80;
    public const ROLE_STAFF_OPD = 88;
    public const ROLE_STAFF_SABERHOAX = 89;
    public const ROLE_STAFF_PROV = 90;
    public const ROLE_PIMPINAN = 91;
    public const ROLE_ADMIN = 99;
    public const ROLE_SERVICE_ACCOUNT = 100; // for third-party app need access (ex: dashboard command center)

    public const STATUS_DELETED = -1;
    public const STATUS_DISABLED = 0;
    public const STATUS_PENDING = 1;
    public const STATUS_ACTIVE = 10;
    public const MAX_ROWS_EXPORT_ALLOWED = 50000;

    // Mapping User role's id type (string to integer)
    public const ROLE_MAP = [
        'admin' => self::ROLE_ADMIN,
        'pimpinan' => self::ROLE_PIMPINAN,
        'staffProv' => self::ROLE_STAFF_PROV,
        'staffSaberhoax' => self::ROLE_STAFF_SABERHOAX,
        'staffOPD' => self::ROLE_STAFF_OPD,
        'staffKabkota' => self::ROLE_STAFF_KABKOTA,
        'staffKec' => self::ROLE_STAFF_KEC,
        'staffKel' => self::ROLE_STAFF_KEL,
        'staffRW' => self::ROLE_STAFF_RW,
        'trainer' => self::ROLE_TRAINER,
        'user' => self::ROLE_USER,
        'service_account_dashboard' => self::ROLE_SERVICE_ACCOUNT,
    ];

    // Constants for Scenario names
    public const SCENARIO_REGISTER = 'register';
    public const SCENARIO_UPDATE = 'update';
    /**
     * Store JWT token header items.
     * @var array
     */
    protected static $decodedToken;
    /** @var  string to store JSON web token */
    public $access_token;
    /** @var  array $permissions to store list of permissions */
    public $permissions;
    /** @var  string string representation of role */
    public $role_id;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user';
    }

    public function getJobType()
    {
        return $this->hasOne(JobType::class, ['id' => 'job_type_id']);
    }

    /**
     * @inheritdoc
     */
    public static function findIdentity($id)
    {
        $user = static::findOne(['id' => $id, 'status' => self::STATUS_ACTIVE]);
        if (
            $user !== null &&
            ($user->getIsBlocked() == true || $user->getIsConfirmed() == false)
        ) {
            return null;
        }
        return $user;
    }

    // explicitly list every field, best used when you want to make sure the changes
    // in your DB table or model attributes do not cause your field changes (to keep API backward compatibility).

    /**
     * @return bool Whether the user is blocked or not.
     */
    public function getIsBlocked()
    {
        return $this->blocked_at != null;
    }

    /**
     * @return bool Whether the user is confirmed or not.
     */
    public function getIsConfirmed()
    {
        return $this->confirmed_at != null;
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        $user = static::findOne(['username' => $username, 'status' => self::STATUS_ACTIVE]);
        if (
            $user !== null &&
            ($user->getIsBlocked() == true || $user->getIsConfirmed() == false)
        ) {
            return null;
        }

        return $user;
    }

    /**
     * Finds user by username
     *
     * @param string $usernamet
     * @param array $roles
     * @return static|null
     */
    public static function findByUsernameWithRoles($username, $roles)
    {
        /** @var User $user */
        $user = static::find()->where(['or',
            ['BINARY(`username`)' => $username],
            ['phone' => $username]
        ])->andWhere(['in', 'role', $roles])->one();

        if (
            $user !== null &&
            ($user->getIsBlocked() == true || $user->getIsConfirmed() == false)
        ) {
            return null;
        }

        return $user;
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }
        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::STATUS_ACTIVE,
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }
        $timestamp = (int)substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * Logins user by given JWT encoded string. If string is correctly decoded
     * - array (token) must contain 'jti' param - the id of existing user
     * @param  string $accessToken access token to decode
     * @return mixed|null          User model or null if there's no user
     * @throws \yii\web\ForbiddenHttpException if anything went wrong
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        $secret = static::getSecretKey();
        // Decode token and transform it into array.
        // Firebase\JWT\JWT throws exception if token can not be decoded
        try {
            $decoded = JWT::decode($token, $secret, [static::getAlgo()]);
        } catch (\Exception $e) {
            return false;
        }
        static::$decodedToken = (array)$decoded;
        // If there's no jti param - exception
        if (!isset(static::$decodedToken['jti'])) {
            return false;
        }
        // JTI is unique identifier of user.
        // For more details: https://tools.ietf.org/html/rfc7519#section-4.1.7
        $id = static::$decodedToken['jti'];
        return static::findByJTI($id);
    }

    protected static function getSecretKey()
    {
        return Yii::$app->params['jwtSecretCode'];
    }

    /**
     * Getter for encryption algorytm used in JWT generation and decoding
     * Override this method to set up other algorytm.
     * @return string needed algorytm
     */
    public static function getAlgo()
    {
        return 'HS256';
    }

    /**
     * Finds User model using static method findOne
     * Override this method in model if you need to complicate id-management
     * @param  string $id if of user to search
     * @return mixed       User model
     */
    public static function findByJTI($id)
    {
        /** @var User $user */
        $user = static::find()->where([
            '=',
            'id',
            $id
        ])
            ->andWhere([
                '=',
                'status',
                self::STATUS_ACTIVE
            ])
            ->andWhere([
                '>',
                'access_token_expired_at',
                new Expression('UNIX_TIMESTAMP()')
            ])->one();
        if (
            $user !== null &&
            ($user->getIsBlocked() == true || $user->getIsConfirmed() == false)
        ) {
            return null;
        }
        return $user;
    }

    public function getLastAccessAtField()
    {
        return $this->last_access_at !== null ? (new Carbon($this->last_access_at))->timestamp : null;
    }

    public function getUsernameUpdatePopUpAtField()
    {
        return $this->username_update_popup_at !== null ? (new Carbon($this->username_update_popup_at))->timestamp : null;
    }

    public function getIsUsernameUpdatedField()
    {
        return $this->is_username_updated == 1 ? true : false;
    }

    public function getEducationLevelField()
    {
        $configParams = include __DIR__ . '/../config/references/education_level.php';

        $records = new Collection($configParams);

        if ($this->education_level_id === null) {
            return null;
        }

        return $records->where('id', '=', $this->education_level_id)->first();
    }

    /** @inheritdoc */
    public function attributeLabels()
    {
        return [
            'username' => Yii::t('app', \Yii::t('app', 'app.username')),
            'email' => Yii::t('app', 'Email'),
            'password' => Yii::t('app', \Yii::t('app', 'app.password')),
            'role_id' => Yii::t('app', 'app.role'),
            'name' => Yii::t('app', 'app.name'),
            'rt' => Yii::t('app', 'app.rt'),
            'rw' => Yii::t('app', 'app.rw'),
            'kel_id' => Yii::t('app', 'app.kel_id'),
            'kec_id' => Yii::t('app', 'app.kec_id'),
            'kabkota_id' => Yii::t('app', 'app.kabkota_id'),
            'registration_ip' => Yii::t('app', 'Registration ip'),
            'unconfirmed_email' => Yii::t('app', 'New email'),
            'created_at' => Yii::t('app', 'Registration time'),
            'confirmed_at' => Yii::t('app', 'Confirmation time'),
        ];
    }

    /** @inheritdoc */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => time()
            ]
        ];
    }

    public function fields()
    {
        $fields = [
            'id',
            'unique_id',
            'username',
            'email',
            'role_id' => function () {
                return array_search($this->role, self::ROLE_MAP);
            },
            'role_label' => function () {
                return $this->getRoleLabel();
            },
            'status',
            'status_label' => function () {
                $statusLabel = '';
                switch ($this->status) {
                    case self::STATUS_ACTIVE:
                        $statusLabel = Yii::t('app', 'status.active');
                        break;
                    case self::STATUS_PENDING:
                        $statusLabel = Yii::t('app', 'Waiting Confirmation');
                        break;
                    case self::STATUS_DISABLED:
                        $statusLabel = Yii::t('app', 'status.inactive');
                        break;
                    case self::STATUS_DELETED:
                        $statusLabel = Yii::t('app', 'status.deleted');
                        break;
                }
                return $statusLabel;
            },
            'name',
            'phone',
            'address',
            'rt',
            'rw',
            'kel_id',
            'kelurahan' => 'KelurahanField',
            'kec_id',
            'kecamatan' => 'KecamatanField',
            'kabkota_id',
            'kabkota' => 'KabkotaField',
            'lat',
            'lon',
            'photo_url' => function () {
                $publicBaseUrl = Yii::$app->params['storagePublicBaseUrl'];

                return $this->photo_url ? "$publicBaseUrl/$this->photo_url" : null;
            },
            'facebook',
            'twitter',
            'instagram',
            'job_type_id',
            'job_type' => 'jobType',
            'education_level_id',
            'education_level' => 'EducationLevelField',
            'birth_date',
            'last_login_at',
            'last_access_at' => 'LastAccessAtField',
            'password_updated_at',
            'profile_updated_at',
            'created_at',
            'updated_at',
            'username_update_popup_at' => 'UsernameUpdatePopUpAtField',
            'is_username_updated' => 'IsUsernameUpdatedField',
            'username_updated_at',
        ];

        return $fields;
    }

    public function getRoleLabel()
    {
        // TODO get from database instead hardcode
        $roles = [
            self::ROLE_USER => Yii::t('app', 'role.user'),
            self::ROLE_TRAINER => Yii::t('app', 'role.trainer'),
            self::ROLE_STAFF_RW => Yii::t('app', 'role.staffRW'),
            self::ROLE_STAFF_KEL => Yii::t('app', 'role.staffKel'),
            self::ROLE_STAFF_KEC => Yii::t('app', 'role.staffKec'),
            self::ROLE_STAFF_KABKOTA => Yii::t('app', 'role.staffKabkota'),
            self::ROLE_STAFF_OPD => Yii::t('app', 'role.staffOPD'),
            self::ROLE_STAFF_SABERHOAX => Yii::t('app', 'role.staffSaberhoax'),
            self::ROLE_STAFF_PROV => Yii::t('app', 'role.staffProv'),
            self::ROLE_PIMPINAN => Yii::t('app', 'role.pimpinan'),
            self::ROLE_ADMIN => Yii::t('app', 'role.admin'),
            self::ROLE_SERVICE_ACCOUNT => Yii::t('app', 'role.service_account'),
        ];

        return $roles[$this->role];
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $attributes = [
            'username', 'email', 'password', 'status', 'role_id',
            'kabkota_id', 'kec_id', 'kel_id', 'rw', 'rt', 'lat', 'lon', 'permissions',
            'name', 'phone', 'address', 'photo_url', 'facebook', 'twitter', 'instagram',
            'birth_date', 'job_type_id', 'education_level_id',
        ];

        $scenarios[self::SCENARIO_REGISTER] = $attributes;
        $scenarios[self::SCENARIO_UPDATE] = $attributes;
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            [['username', 'email', 'role_id'], 'required', 'on' => self::SCENARIO_REGISTER],
            ['username', 'trim'],
            ['username', 'required'],
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
                'message' => Yii::t('app', 'error.username.taken'),
            ],

            ['email', 'trim'],
            ['email', 'required'],
            ['email', 'string', 'max' => self::MAX_LENGTH],
            ['email', 'email'],
            [
                'email',
                'unique',
                'message' => Yii::t('app', 'error.email.taken'),
            ],

            ['password', 'string', 'length' => [5, self::MAX_LENGTH]],
            ['password', 'validatePasswordSubmit'],
            [['confirmed_at', 'blocked_at', 'last_login_at'], 'datetime', 'format' => 'php:U'],
            [['last_login_ip', 'registration_ip'], 'ip'],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_PENDING, self::STATUS_DISABLED]],

            ['role_id', 'in', 'range' => array_keys(self::ROLE_MAP)],
            ['role_id', 'validateRolePermission'],

            [['access_token', 'permissions'], 'safe'],
            ['phone', 'trim'],
            ['kabkota_id', 'required', 'on' => self::SCENARIO_REGISTER, 'when' => function ($model) {
                return $model->role <= self::ROLE_STAFF_KABKOTA;
            }
            ],
            ['kec_id', 'required', 'on' => self::SCENARIO_REGISTER, 'when' => function ($model) {
                return $model->role <= self::ROLE_STAFF_KEC;
            }
            ],
            ['kel_id', 'required', 'on' => self::SCENARIO_REGISTER, 'when' => function ($model) {
                return $model->role <= self::ROLE_STAFF_KEL;
            }
            ],
            ['rw', 'required', 'on' => self::SCENARIO_REGISTER, 'when' => function ($model) {
                return $model->role <= self::ROLE_STAFF_RW;
            }
            ],
            [
                [
                    'name', 'phone', 'address',
                    'rt', 'rw', 'kel_id', 'kec_id', 'kabkota_id',
                    'lat', 'lon', 'photo_url', 'facebook', 'twitter', 'instagram',
                    'birth_date', 'job_type_id', 'education_level_id',
                ],
                'default'
            ],
            [['name', 'phone', 'address', 'rt', 'rw', 'lat', 'lon', 'photo_url', 'facebook', 'twitter', 'instagram'], 'trim'],
            [['name', 'address'], 'string', 'max' => self::MAX_LENGTH],
            ['phone', 'string', 'length' => [3, 15]],
        ];

        return array_merge(
            $rules,
            $this->rulesRw()
        );
    }

    /**
     * Validate whether password is submitted or not
     *  Only required to submit the password on creation
     *
     * @param $attribute
     * @param $params
     */
    public function validatePasswordSubmit($attribute, $params)
    {
        // get post type - POST or PUT
        $request = Yii::$app->request;

        // if POST, mode is create
        if ($request->isPost) {
            if ($this->$attribute == '') {
                $this->addError($attribute, Yii::t('app', 'The password is required.'));
            }
        } elseif ($request->isPut) {
            // No action required
        }
    }

    public static function findIdentityWithoutValidation($id)
    {
        $user = static::findOne(['id' => $id]);

        return $user;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    /**
     * Confirm Email address
     *      Not implemented Yet
     *
     * @return bool whether the email is confirmed o not
     */
    public function confirmEmail()
    {
        if ($this->unconfirmed_email != '') {
            $this->email = $this->unconfirmed_email;
        }
        $this->registration_ip = Yii::$app->request->userIP;
        $this->status = self::STATUS_ACTIVE;
        $this->account_confirmed_at = new Expression('NOW()');
        $this->save(false);
        $this->touch('confirmed_at');

        return true;
    }

    /**
     * Generate access token
     *  This function will be called every on request to refresh access token.
     *
     * @param bool $forceRegenerate whether regenerate access token even if not expired
     *
     * @return bool whether the access token is generated or not
     */
    public function generateAccessTokenAfterUpdatingClientInfo($forceRegenerate = false)
    {
        // update client login, ip
        $this->last_login_ip = Yii::$app->request->getUserIP();
        $this->last_login_at = new Expression('UNIX_TIMESTAMP()');

        // check time is expired or not
        if (
            $forceRegenerate == true
            || $this->access_token_expired_at == null
            || (time() > $this->access_token_expired_at)
        ) {
            // generate access token
            $this->generateAccessToken();
        }
        $this->save(false);
        return true;
    }

    public function generateAccessToken()
    {
        // generate access token
//        $this->access_token = Yii::$app->security->generateRandomString();
        $tokens = $this->getJWT();
        $this->access_token = $tokens[0];   // Token
        $this->access_token_expired_at = $tokens[1]['exp']; // Expire
    }

    /*
     * JWT Related Functions
     */

    /**
     * Encodes model data to create custom JWT with model.id set in it
     * @return array encoded JWT
     */
    public function getJWT()
    {
        $expirationDuration = 0;
        if (in_array($this->role, LoginForm::USER_ROLES)) {
            $expirationDuration = LoginForm::LOGIN_DURATION_USER;
        } else {
            $expirationDuration = LoginForm::LOGIN_DURATION_STAFF;
        }

        // Collect all the data
        $secret = static::getSecretKey();
        $currentTime = time();
        $expire = $currentTime + $expirationDuration;
        $request = Yii::$app->request;
        $hostInfo = '';
        // There is also a \yii\console\Request that doesn't have this property
        if ($request instanceof WebRequest) {
            $hostInfo = $request->hostInfo;
        }

        // Merge token with presets not to miss any params in custom
        // configuration
        $token = array_merge([
            'iat' => $currentTime,
            // Issued at: timestamp of token issuing.
            'iss' => $hostInfo,
            // Issuer: A string containing the name or identifier of the issuer application. Can be a domain name and can be used to discard tokens from other applications.
            'aud' => $hostInfo,
            'nbf' => $currentTime,
            // Not Before: Timestamp of when the token should start being considered valid. Should be equal to or greater than iat. In this case, the token will begin to be valid 10 seconds
            'exp' => $expire,
            // Expire: Timestamp of when the token should cease to be valid. Should be greater than iat and nbf. In this case, the token will expire 60 seconds after being issued.
            'data' => [
                'username' => $this->username,
                'roleLabel' => $this->getRoleLabel(),
                'lastLoginAt' => $this->last_login_at,
            ]
        ], static::getHeaderToken());
        // Set up id
        $token['jti'] = $this->getJTI();    // JSON Token ID: A unique string, could be used to validate a token, but goes against not having a centralized issuer authority.
        return [JWT::encode($token, $secret, static::getAlgo()), $token];
    }

    protected static function getHeaderToken()
    {
        return [];
    }

    // And this one if you wish

    /**
     * Returns some 'id' to encode to token. By default is current model id.
     * If you override this method, be sure that findByJTI is updated too
     * @return integer any unique integer identifier of user
     */
    public function getJTI()
    {
        return $this->getId();
    }

    public function beforeSave($insert)
    {
        // Convert username to lower case
        $this->username = strtolower($this->username);

        // Fill unconfirmed email field with email if empty
        if ($this->unconfirmed_email == '') {
            $this->unconfirmed_email = $this->email;
        }

        // Set confirmed_at with current timestamp, since there's no 'confirmation email' feature yet
        $this->confirmed_at = Yii::$app->formatter->asTimestamp(date('Y-m-d H:i:s'));

        // Fill registration ip with current ip address if empty
        if ($this->registration_ip === '' && Yii::$app instanceof \yii\web\Application) {
            $this->registration_ip = Yii::$app->request->userIP;
        }

        // Fill auth key if empty
        if ($this->auth_key == '') {
            $this->generateAuthKey();
        }

        // Set password if not null
        if ($this->password != '') {
            $this->setPassword($this->password);
        }

        return parent::beforeSave($insert);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    public function afterSave($insert, $changedAttributes)
    {
        $authManager = Yii::$app->authManager;

        // ---- Start to process role
        // When insert new user, assign new role
        if ($insert === true) {
            $roleName = $this->getRoleName();

            $authItem = $authManager->getRole($roleName);
            $authManager->assign($authItem, $this->getId());
        } else {
            // When update existing user, revoke old role and assign new role
            if (isset($changedAttributes['role']) === true) {
                // Get role name
                $roleName = $this->getRoleName();
                $authManager->revokeAll($this->getId());
                $authItem = $authManager->getRole($roleName);
                $authManager->assign($authItem, $this->getId());
            }
        }
        // ---- Finish to process role

        return parent::afterSave($insert, $changedAttributes);
    }

    /**
     * Validate if authenticated user has permission to assign a specific role
     *
     * @param $attribute
     * @param $params
     */
    public function validateRolePermission($attribute, $params)
    {
        $this->role = self::ROLE_MAP[$this->$attribute];

        $currentUser = User::findIdentity(\Yii::$app->user->getId());
        if ($currentUser->role < self::ROLE_ADMIN && $currentUser->role <= $this->role) {
            $this->addError($attribute, Yii::t('app', 'error.role.permission'));
        }
    }

    public function getRoleName()
    {
        $roles = [
            self::ROLE_USER => 'user',
            self::ROLE_TRAINER => 'trainer',
            self::ROLE_STAFF_RW => 'staffRW',
            self::ROLE_STAFF_KEL => 'staffKel',
            self::ROLE_STAFF_KEC => 'staffKec',
            self::ROLE_STAFF_KABKOTA => 'staffKabkota',
            self::ROLE_STAFF_OPD => 'staffOPD',
            self::ROLE_STAFF_SABERHOAX => 'staffSaberhoax',
            self::ROLE_STAFF_PROV => 'staffProv',
            self::ROLE_PIMPINAN => 'pimpinan',
            self::ROLE_ADMIN => 'admin',
            self::ROLE_SERVICE_ACCOUNT => 'service_account_dashboard',
        ];

        return $roles[$this->role];
    }

    public function getPassword()
    {
        return '';
    }

    // Functions related to user push token

    public function removePushToken()
    {
        if ($this->push_token) {
            // Area ids will be used as topic name
            $areaIds = [
                (string) $this->kabkota_id,
                (string) $this->kec_id,
                (string) $this->kel_id,
                "{$this->kel_id}_{$this->rw}",
            ];

            Message::unsubscribe($this->push_token, $areaIds);
            Message::unsubscribe($this->push_token, [Notification::TOPIC_DEFAULT]);
            $this->push_token = null;
            $this->save(false);
        }
    }

    public function updatePushToken($pushToken)
    {
        if ($this->push_token != $pushToken) {
            $this->removePushToken();

            $this->push_token = $pushToken;

            // Area ids will be used as topic name
            $areaIds = [
                (string) $this->kabkota_id,
                (string) $this->kec_id,
                (string) $this->kel_id,
                "{$this->kel_id}_{$this->rw}",
            ];

            Message::subscribe($pushToken, [Notification::TOPIC_DEFAULT]);
            Message::subscribe($pushToken, $areaIds);
        }
    }

    public function hasPushToken()
    {
        // Use '==' to acommodate falsy values, e.g. null and empty string
        return $this->push_token == true;
    }
}
