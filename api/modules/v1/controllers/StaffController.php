<?php

namespace app\modules\v1\controllers;

use app\components\UserTrait;
use app\filters\auth\HttpBearerAuth;
use app\models\User;
use app\models\UserImportCsvUploadForm;
use app\models\UserSearch;
use app\models\UserExport;
use app\modules\v1\controllers\Concerns\UserPhotoUpload;
use Jdsteam\Sapawarga\Filters\RecordLastActivity;
use Jdsteam\Sapawarga\Jobs\ImportUserJob;
use Yii;
use yii\base\InvalidConfigException;
use yii\filters\AccessControl;
use yii\filters\auth\CompositeAuth;
use yii\helpers\Url;
use yii\rbac\Permission;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
<<<<<<< HEAD
=======
use Box\Spout\Common\Entity\Row;
use Carbon\Carbon;
use creocoder\flysystem\Filesystem;
use yii\web\UploadedFile;
>>>>>>> [Import User] handling HTTP post upload file and dispatch job

class StaffController extends ActiveController
{
    use UserTrait, UserPhotoUpload;

    public $modelClass = 'app\models\User';

    public function __construct($id, $module, $config = [])
    {
        parent::__construct($id, $module, $config);
    }

    public function actions()
    {
        return [];
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            'authMethods' => [
                HttpBearerAuth::className(),
            ],

        ];

        $behaviors['verbs'] = [
            'class' => \yii\filters\VerbFilter::className(),
            'actions' => [
                'index' => ['get'],
                'view' => ['get'],
                'create' => ['post'],
                'update' => ['put'],
                'delete' => ['delete'],
                'login' => ['post'],
                'count' => ['get'],
                'getPermissions' => ['get'],
                'photo-upload' => ['post'],
                'me' => ['get', 'post'],
                'export' => ['get'],
                'import' => ['post'],
            ],
        ];

        $behaviors['recordLastActivity'] = [
            'class' => RecordLastActivity::class,
        ];

        // remove authentication filter
        $auth = $behaviors['authenticator'];
        unset($behaviors['authenticator']);

        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::className(),
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
            ],
        ];

        // re-add authentication filter
        $behaviors['authenticator'] = $auth;
        // avoid authentication on CORS-pre-flight requests (HTTP OPTIONS method)
        $behaviors['authenticator']['except'] = ['options', 'login'];

        // setup access
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => ['index', 'view', 'create', 'update', 'delete', 'photo-upload', 'getPermissions'], //only be applied to
            'rules' => [
                [
                    'allow' => true,
                    'actions' => [
                        'index', 'view', 'create',
                        'update', 'delete', 'me', 'count', 'photo-upload',
                        'import',
                        'getPermissions',
                    ],
                    'roles' => ['admin', 'manageStaffs'],
                ],
            ],
        ];

        return $behaviors;
    }

    /**
     * Search staff
     *
     * @return array
     * @throws BadRequestHttpException
     */
    public function actionIndex()
    {
        $roles = [];
        $currentUser = User::findIdentity(\Yii::$app->user->getId());
        $role = $currentUser->role;
        // Admins can see other admins, while staffs can only see staffs one level below them
        $maxRoleRange = ($role == User::ROLE_ADMIN) ? ($role) : ($role - 1);

        $search = new UserSearch();
        $search->load(\Yii::$app->request->get());
        $search->range_roles = [0, $maxRoleRange];
        $search->not_in_status = [User::STATUS_DELETED];

        // If search parameters are null, use current user's area ids
        $search->kabkota_id = $search->kabkota_id ?? $currentUser->kabkota_id;
        $search->kec_id = $search->kec_id ?? $currentUser->kec_id;
        $search->kel_id = $search->kel_id ?? $currentUser->kel_id;
        $search->rw = $search->rw ?? $currentUser->rw;

        // Only admin can see saberhoax
        $search->show_saberhoax = ($currentUser->role == User::ROLE_ADMIN) ? true : false;

        if (!$search->validate()) {
            throw new BadRequestHttpException(
                'Invalid parameters: ' . json_encode($search->getErrors())
            );
        }

        return $search->getDataProvider();
    }

    /**
     * User Export to csv
     *
     * @return string URL
     * @throws ServerErrorHttpException
     */
    public function actionExport()
    {
        # Get data users
        $currentUser = User::findIdentity(\Yii::$app->user->getId());
        $role = $currentUser->role;
        $maxRoleRange = ($role == User::ROLE_ADMIN) ? ($role) : ($role - 1);

        $params = Yii::$app->request->getQueryParams();
        $params['max_roles'] = $maxRoleRange;
        $params['show_saberhoax'] = ($currentUser->role == User::ROLE_ADMIN) ? true : false;
        $params['kabkota_id'] = $params['kabkota_id'] ?? $currentUser->kabkota_id;
        $params['kec_id'] = $params['kec_id'] ?? $currentUser->kec_id;
        $params['kel_id'] = $params['kel_id'] ?? $currentUser->kel_id;
        $params['rw'] = $params['rw'] ?? $currentUser->rw;

        $search = new UserExport();

        // Check validation max record
        $totalRows = $search->getUserExport($params)->count();
        if ($totalRows > User::MAX_ROWS_EXPORT_ALLOWED) {
            throw new ServerErrorHttpException("User export have $totalRows rows, max rows is " . User::MAX_ROWS_EXPORT_ALLOWED);
        }

        // Initial varieble location, filename, path
        $publicBaseUrl = Yii::$app->params['storagePublicBaseUrl'];
        $nowDate = date('Y-m-d-H-i-s');
        $filename = "export-user-$nowDate.csv";
        $filenameTemp = "temp-export-user-$nowDate.csv";
        $filePathTemp = Yii::getAlias('@webroot/storage') . '/' . $filenameTemp;

        // Write to temp file
        $writer = WriterEntityFactory::createCSVWriter($filePathTemp);
        $writer->setFieldDelimiter(',');
        $writer->setFieldEnclosure('"');
        $writer->setShouldAddBOM(false);
        $writer->openToFile($filePathTemp);

        $titleRow = ['id', 'role', 'username', 'name', 'email', 'confirmed_at', 'status', 'created_at', 'updated_at', 'phone', 'address', 'kabkota', 'kecamatan', 'kelurahan', 'rw', 'rt', 'password_updated_at', 'profile_updated_at', 'last_access_at'];

        $writer->addRow(WriterEntityFactory::createRowFromArray($titleRow));

        $search = $search->getUserExport($params);
        foreach ($search->each() as $key => $user) {
            $row = [
                $user['id'],
                $user['role'],
                $user['username'],
                $user['name'],
                $user['email'],
                $user['confirmed_at'],
                $user['status'],
                $user['created_at'],
                $user['updated_at'],
                $user['phone'],
                $user['address'],
                $user['kabkota_name'],
                $user['kec_name'],
                $user['kel_name'],
                $user['rw'],
                $user['rt'],
                $user['password_updated_at'],
                $user['profile_updated_at'],
                $user['last_access_at'],
            ];
            $writer->addRow(WriterEntityFactory::createRowFromArray($row));
        }

        // Open temp and save to flysystem
        $stream = fopen($filePathTemp, 'r+');
        Yii::$app->fs->writeStream($filename, $stream);
        unlink($filePathTemp);

        // Return file url
        $filePath = $publicBaseUrl . '/' . $filename;

        return $filePath;
    }

    public function actionImport()
    {
        $currentUser = User::findIdentity(Yii::$app->user->getId());

        $model       = new UserImportCsvUploadForm();
        $model->file = UploadedFile::getInstanceByName('file');

        if ($model->validate() === false) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(422);

            return $model->getErrors();
        }

        // Upload to S3 and push new queue job for async/later processing
        if ($filePath = $model->upload()) {
            $this->pushQueueJob($currentUser, $filePath);

            return ['file_path' => $filePath];
        }

        throw new ServerErrorHttpException('Failed to upload the object for unknown reason.');
    }

    protected function pushQueueJob($user, $filePath)
    {
        Yii::$app->queue->push(new ImportUserJob([
            'filePath'      => $filePath,
            'uploaderEmail' => $user->email,
        ]));
    }

    /**
     * Create new staff member from backend dashboard
     *
     * Request: POST /v1/staff/1
     *
     * @return User
     * @throws HttpException
     * @throws InvalidConfigException
     */
    public function actionCreate()
    {
        $model = new User();
        $model->scenario = User::SCENARIO_REGISTER;
        $model->load(\Yii::$app->getRequest()->getBodyParams(), '');

        if ($model->validate() && $model->save()) {
            $response = \Yii::$app->getResponse();
            $response->setStatusCode(201);
            $id = implode(',', array_values($model->getPrimaryKey(true)));
            $response->getHeaders()->set('Location', Url::toRoute([$id], true));
        } else {
            // Validation error
            $response = \Yii::$app->getResponse();
            $response->setStatusCode(422);

            return $model->getErrors();
        }

        return $model;
    }

    /**
     * Update staff member information from backend dashboard
     *
     * Request: PUT /v1/staff/1
     *
     * @param $id
     *
     * @return array|null|\yii\db\ActiveRecord
     */
    public function actionUpdate($id)
    {
        $model = $this->actionView($id);
        $model->scenario = User::SCENARIO_UPDATE;
        $model->load(\Yii::$app->getRequest()->getBodyParams(), '');

        if ($model->validate() && $model->save()) {
            $response = \Yii::$app->getResponse();
            $response->setStatusCode(200);
        } else {
            // Validation error
            $response = \Yii::$app->getResponse();
            $response->setStatusCode(422);

            return $model->getErrors();
        }

        return $model;
    }

    /**
     * Update logged in user information
     *
     * @return array|null|\yii\db\ActiveRecord
     *
     */
    public function actionMeUpdate()
    {
        return $this->updateCurrentUser();
    }

    /**
     * Return requested staff member information
     *
     * Request: /v1/staff/2
     *
     * @param $id
     *
     * @return array|null|\yii\db\ActiveRecord
     * @throws NotFoundHttpException
     */
    public function actionView($id)
    {
        $currentUser = User::findIdentity(\Yii::$app->user->getId());
        $role = $currentUser->role;
        // Admins can see other admins, while staffs can only see staffs one level below them
        $maxRoleRange = ($role == User::ROLE_ADMIN) ? ($role) : ($role - 1);

        $staff = User::find()->where(
            [
                'id' => $id
            ]
        )->andWhere(
            [
                '!=',
                'status',
                User::STATUS_DELETED
            ]
        )->andWhere(
            [
                'or',
                ['between', 'role', 0, $maxRoleRange],
                $id . '=' . (string) $currentUser->id
            ]
        )->one();
        if ($staff) {
            return $staff;
        } else {
            throw new NotFoundHttpException("Object not found: $id");
        }
    }

    /**
     * Return logged in user information
     *
     * @return array
     * @throws NotFoundHttpException
     */
    public function actionMe()
    {
        return $this->getCurrentUser();
    }

    /**
     * Delete requested staff member from backend dashboard
     *
     * Request: DELETE /v1/staff/1
     *
     * @param $id
     *
     * @return string
     * @throws ServerErrorHttpException
     * @throws NotFoundHttpException
     */
    public function actionDelete($id)
    {
        $model = $this->actionView($id);

        return $this->applySoftDelete($model);
    }

    /**
     * Handle the login process for staff members for backend dashboard
     *
     * Request: POST /v1/staff/login
     *
     *
     * @return array
     * @throws HttpException
     */
    public function actionLogin()
    {
        $roles = [
            User::ROLE_ADMIN,
            User::ROLE_STAFF_PROV,
            User::ROLE_STAFF_KABKOTA,
            User::ROLE_STAFF_KEC,
            User::ROLE_STAFF_KEL,
            User::ROLE_STAFF_SABERHOAX,
        ];
        return $this->login($roles);
    }

    /**
     * Return number of users, depending on role of the logged-in staff
     *
     * Request: GET /v1/staff/count
     */
    public function actionCount()
    {
        $roleMap = [
            User::ROLE_ADMIN => ['level' => 'all', 'name' => 'Semua'],
            User::ROLE_STAFF_PROV => ['level' => 'prov', 'name' => 'Provinsi'],
            User::ROLE_STAFF_KABKOTA => ['level' => 'kabkota', 'name' => 'Kabupaten/Kota'],
            User::ROLE_STAFF_KEC => ['level' => 'kec', 'name' => 'Kecamatan'],
            User::ROLE_STAFF_KEL => ['level' => 'kel', 'name' => 'Desa/Kelurahan'],
            User::ROLE_STAFF_RW => ['level' => 'rw', 'name' => 'RW'],
            User::ROLE_TRAINER => ['level' => 'trainer', 'name' => 'Pelatih'],
        ];

        $currentUser = User::findIdentity(\Yii::$app->user->getId());

        if ($currentUser->role >= User::ROLE_STAFF_PROV) {
            $roleMap[User::ROLE_STAFF_SABERHOAX] = ['level' => 'saberhoax', 'name' => 'Saber Hoax'];
        }

        $kabkota_id = $currentUser->kabkota_id;
        $kel_id = $currentUser->kel_id;
        $kec_id = $currentUser->kec_id;
        $rw = $currentUser->rw;
        $role = $currentUser->role;

        // Admin will get all user counts, while staffs below admin will get user counts only from areas below them
        $items = [];
        $index = 1;
        foreach ($roleMap as $key => $value) {
            if ($role == User::ROLE_ADMIN ||
                $role < User::ROLE_ADMIN && $key < $role
            ) {
                $query = User::find();
                if ($key < User::ROLE_ADMIN) {
                    $query->where(['role' => $key]);
                }

                // filter by area (for staffProv and below)
                if ($kabkota_id) {
                    $query->andWhere(['kabkota_id' => $kabkota_id]);
                }
                if ($kec_id) {
                    $query->andWhere(['kec_id' => $kec_id]);
                }
                if ($kel_id) {
                    $query->andWhere(['kel_id' => $kel_id]);
                }
                if ($rw) {
                    $query->andWhere(['rw' => $rw]);
                }

                $count = $query->count();
                array_push($items, [
                    'id' => $index,
                    'level' => $value['level'],
                    'name' => $value['name'],
                    'value' => (int) $count,
                ]);
                $index++;
            }
        }

        return ['items' => $items];
    }

    /**
     * Return list of available permissions for the staff.  The function will be called when staff form is loaded in backend.
     *
     * Request: GET /v1/staff/get-permissions
     *
     * Sample Response:
     * {
     *        "success": true,
     *        "status": 200,
     *        "data": {
     *            "manageSettings": {
     *                "name": "manageSettings",
     *                "description": "Manage settings",
     *                "checked": false
     *            },
     *            "manageStaffs": {
     *                "name": "manageStaffs",
     *                "description": "Manage staffs",
     *                "checked": false
     *            }
     *        }
     *    }
     */
    public function actionGetPermissions()
    {
        $authManager = Yii::$app->authManager;

        /** @var Permission[] $permissions */
        $permissions = $authManager->getPermissions();

        /** @var array $tmpPermissions to store list of available permissions */
        $tmpPermissions = [];

        /**
         * @var string $permissionKey
         * @var Permission $permission
         */
        foreach ($permissions as $permissionKey => $permission) {
            $tmpPermissions[] = [
                'name' => $permission->name,
                'description' => $permission->description,
                'checked' => false,
            ];
        }

        return $tmpPermissions;
    }

    public function actionPhotoUpload()
    {
        return $this->processPhotoUpload();
    }

    /**
     * Handle OPTIONS
     *
     * @param null $id
     * @return string
     */
    public function actionOptions($id = null)
    {
        return 'ok';
    }
}
