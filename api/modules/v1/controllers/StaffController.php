<?php

namespace app\modules\v1\controllers;

use app\components\UserTrait;
use app\filters\auth\HttpBearerAuth;
use app\models\User;
use app\models\UserExport;
use app\models\UserImport;
use app\models\UserImportCsvUploadForm;
use app\models\UserSearch;
use app\modules\v1\controllers\Concerns\UserPhotoUpload;
use app\modules\v1\repositories\UserRepository;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Illuminate\Support\Arr;
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
use yii\web\UploadedFile;

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
            'only' => [
                'index', 'view', 'create', 'update', 'delete',
                'photo-upload', 'import', 'import-template', 'getPermissions'
            ],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => [
                        'index', 'view', 'create',
                        'update', 'delete', 'me', 'count', 'photo-upload',
                        'import', 'import-template',
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
        $params['show_saberhoax'] = $currentUser->role == User::ROLE_ADMIN ? 'yes' : 'no';
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

    public function actionImportTemplate()
    {
        $response = Yii::$app->getResponse();

        $filePath = UserImport::generateTemplateFile();

        if (file_exists($filePath) === false) {
            return $response->setStatusCode(404);
        }

        $fileUrl = $this->copyTemplateToStorage($filePath);

        return ['file_url' => $fileUrl];
    }

    protected function copyTemplateToStorage($sourcePath)
    {
        $destinationPath = 'template-users-import.csv';

        $contents = file_get_contents($sourcePath);

        Yii::$app->fs->put($destinationPath, $contents);

        $fileUrl = sprintf('%s/%s', Yii::$app->params['storagePublicBaseUrl'], $destinationPath);

        return $fileUrl;
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
     * @throws \yii\base\Exception
     */
    public function actionMeUpdate()
    {
        $allowedAttributes = [
            'username', 'email', 'password', 'name', 'phone', 'address',
            'job_type_id', 'education_level_id', 'birth_date', 'rt', 'rw',
            'lat', 'lon', 'photo_url', 'facebook', 'twitter', 'instagram',
            'status', 'role', 'kabkota_id', 'kec_id', 'kel_id',
        ];

        $attributes = Yii::$app->request->post('UserEditForm');

        return $this->updateCurrentUser(Arr::only($attributes, $allowedAttributes));
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
        $currentUser = User::findIdentity(Yii::$app->user->getId());

        $filterKabkotaId = $currentUser->kabkota_id;
        $filterKecId     = $currentUser->kec_id;
        $filterKelId     = $currentUser->kel_id;

        $repository    = new UserRepository();

        // TODO should not hard coded here
        $selectedRoles = ['staffProv', 'staffKabkota', 'staffKec', 'staffKel', 'staffRW', 'staffSaberhoax', 'trainer'];

        $counters = $repository->getUsersCountAllRolesByArea(
            $selectedRoles,
            $filterKabkotaId,
            $filterKecId,
            $filterKelId
        );

        return ['items' => $counters];
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
