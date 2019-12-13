<?php

namespace app\modules\v1\controllers;

use app\components\UserTrait;
use app\filters\auth\HttpBearerAuth;
use app\models\User;
use app\models\UserSearch;
use app\modules\v1\controllers\Concerns\UserPhotoUpload;
use app\modules\v1\repositories\UserRepository;
use Illuminate\Support\Arr;
use Jdsteam\Sapawarga\Filters\RecordLastActivity;
use Yii;
use yii\base\InvalidConfigException;
use yii\filters\AccessControl;
use yii\filters\auth\CompositeAuth;
use yii\helpers\Url;
use yii\rbac\Permission;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

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
                'photo-upload', 'getPermissions'
            ],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => [
                        'index', 'view', 'create',
                        'update', 'delete', 'me', 'count', 'photo-upload',
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

        $search->show_trainer = false;
        if (in_array($currentUser->role, [User::ROLE_STAFF_PROV, User::ROLE_ADMIN])) {
            $search->show_trainer = true;
        }

        $search->show_user = false;
        if (in_array($currentUser->role, [User::ROLE_STAFF_PROV, User::ROLE_ADMIN])) {
            $search->show_user = true;
        }

        if (!$search->validate()) {
            throw new BadRequestHttpException(
                'Invalid parameters: ' . json_encode($search->getErrors())
            );
        }

        return $search->getDataProvider();
    }

    public function findModel($id)
    {
        $model = User::find()
            ->where(['id' => $id])
            ->andWhere(['!=', 'status', User::STATUS_DELETED])
            ->one();

        if (isset($model)) {
            return $model;
        }

        throw new NotFoundHttpException("Object not found: $id");
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
        if (Yii::$app->user->can('create_user') === false) {
            throw new ForbiddenHttpException(Yii::t('app', 'error.role.permission'));
        }

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
        $model = $this->findModel($id);

        if (Yii::$app->user->can('edit_user', ['record' => $model]) === false) {
            throw new ForbiddenHttpException(Yii::t('app', 'error.role.permission'));
        }

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
        $model = $this->findModel($id);

        if (Yii::$app->user->can('view_user', ['record' => $model]) === false) {
            throw new ForbiddenHttpException(Yii::t('app', 'error.role.permission'));
        }

        return $model;
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
        $model = $this->findModel($id);

        if (Yii::$app->user->can('delete_user', ['record' => $model]) === false) {
            throw new ForbiddenHttpException(Yii::t('app', 'error.role.permission'));
        }

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
        $currentUserId    = Yii::$app->user->getId();
        $currentUser      = User::findIdentity($currentUserId);

        $currentUserRoles = Yii::$app->authManager->getRolesByUser($currentUserId);
        $currentUserRole  = Arr::first($currentUserRoles);

        $filterKabkotaId = $currentUser->kabkota_id;
        $filterKecId     = $currentUser->kec_id;
        $filterKelId     = $currentUser->kel_id;

        $repository    = new UserRepository();

        $selectedRoles = $repository->getDescendantRoles($currentUserRole->name);

        $items = $repository->getUsersCountAllRolesByArea(
            $selectedRoles,
            $filterKabkotaId,
            $filterKecId,
            $filterKelId
        );

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
