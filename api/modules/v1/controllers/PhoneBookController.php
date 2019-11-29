<?php

namespace app\modules\v1\controllers;

use app\models\PhoneBook;
use app\models\PhoneBookSearch;
use app\models\User;
use Illuminate\Support\Arr;
use Yii;
use yii\base\Model;
use yii\db\Expression;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

/**
 * PhoneBookController implements the CRUD actions for PhoneBook model.
 */
class PhoneBookController extends ActiveController
{
    public $modelClass = PhoneBook::class;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['verbs'] = [
            'class'   => \yii\filters\VerbFilter::className(),
            'actions' => [
                'index'  => ['get'],
                'view'   => ['get'],
                'create' => ['post'],
                'update' => ['put'],
                'delete' => ['delete'],
                'public' => ['get'],
                'check-exist' => ['get'],
                'by-user-location' => ['get'],
            ],
        ];

        return $this->behaviorCors($behaviors);
    }

    protected function behaviorAccess($behaviors)
    {
        // setup access
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only'  => ['index', 'view', 'create', 'update', 'delete', 'check-exist', 'by-user-location'],
            'rules' => [
                [
                    'allow'   => true,
                    'actions' => ['index', 'view', 'create', 'update', 'delete', 'check-exist', 'by-user-location'],
                    'roles'   => ['admin', 'manageStaffs'],
                ],
                [
                    'allow'   => true,
                    'actions' => ['index', 'view', 'by-user-location'],
                    'roles'   => ['user', 'staffRW'],
                ],
            ],
        ];

        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();

        // Override Delete Action
        unset($actions['delete']);

        // Override Create Action
        unset($actions['create']);

        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];
        $actions['view']['findModel'] = [$this, 'findModel'];

        return $actions;
    }

    public function actionCreate()
    {
        /* @var $model \yii\db\ActiveRecord */
        $model = new $this->modelClass([
            'scenario' => Model::SCENARIO_DEFAULT,
        ]);

        $model->status = PhoneBook::STATUS_ACTIVE;

        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        if ($model->save()) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(201);
        } elseif (!$model->hasErrors()) {
            throw new ServerErrorHttpException('Failed to create the object for unknown reason.');
        }

        return $model;
    }

    /**
     * Delete entity with soft delete / status flagging
     *
     * @param $id
     * @return string
     * @throws \yii\web\ForbiddenHttpException
     * @throws \yii\web\NotFoundHttpException
     * @throws \yii\web\ServerErrorHttpException
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        $this->checkAccess('delete', $model, $id);

        return $this->applySoftDelete($model);
    }

    public function actionCheckExist()
    {
        $params = Yii::$app->request->getQueryParams();
        $phoneNumber = Arr::get($params, 'phone_number');

        if (empty($phoneNumber)) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(422);

            return 'Query Params phone_number is required.';
        }

        $expression = new Expression("JSON_CONTAINS(phone_numbers->'$[*].phone_number', json_array('$phoneNumber'))");

        $model = PhoneBook::find()
            ->andWhere($expression)
            ->andWhere(['!=', 'status', PhoneBook::STATUS_DELETED])
            ->one();

        $response = Yii::$app->getResponse();
        $response->setStatusCode(200);

        if ($model !== null) {
            return ['exist' => true];
        }

        return ['exist' => false];
    }

    /**
     *  Filter phone book by user location (kabkota_id)
     */
    public function actionByUserLocation()
    {
        $userDetail = User::findIdentity(Yii::$app->user->getId());

        if ($userDetail === null) {
            throw new NotFoundHttpException('User detail not found');
        }

        $params = Yii::$app->request->getQueryParams();
        $instansi = Arr::get($params, 'instansi');

        if (empty($instansi)) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(422);

            return 'Query Params instansi is required.';
        }

        $model = PhoneBook::find()
            ->where(['kabkota_id' => $userDetail->kabkota_id])
            ->andWhere(['like', 'name', $instansi])
            ->andWhere(['!=', 'status', PhoneBook::STATUS_DELETED])
            ->one();

        $response = Yii::$app->getResponse();
        $response->setStatusCode(200);

        return $model;
    }

    /**
     * Checks the privilege of the current user.
     *
     * This method should be overridden to check whether the current user has the privilege
     * to run the specified action against the specified data model.
     * If the user does not have access, a [[ForbiddenHttpException]] should be thrown.
     *
     * @param string $action the ID of the action to be executed
     * @param object $model the model to be accessed. If null, it means no specific model is being accessed.
     * @param array $params additional parameters
     */
    public function checkAccess($action, $model = null, $params = [])
    {
        // throw new ForbiddenHttpException();
    }

    /**
     * @param $id
     * @return mixed|PhoneBook
     * @throws \yii\web\NotFoundHttpException
     */
    public function findModel($id)
    {
        $model = PhoneBook::find()
            ->where(['id' => $id])
            ->andWhere(['!=', 'status', PhoneBook::STATUS_DELETED])
            ->one();

        if ($model === null) {
            throw new NotFoundHttpException("Object not found: $id");
        }

        return $model;
    }

    public function prepareDataProvider()
    {
        $search = new PhoneBookSearch();

        $user = User::findIdentity(Yii::$app->user->getId());

        return $search->search($user, Yii::$app->request->getQueryParams());
    }
}
