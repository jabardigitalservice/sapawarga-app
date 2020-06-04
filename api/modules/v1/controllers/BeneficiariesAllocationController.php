<?php

namespace app\modules\v1\controllers;

use app\models\BeneficiaryAllocation;
use app\models\BeneficiaryAllocationSearch;
use Yii;
use yii\filters\AccessControl;

/**
 * BeneficiariesAllocationController implements the CRUD actions for Beneficiary model.
 */
class BeneficiariesAllocationController extends ActiveController
{
    public $modelClass = BeneficiaryAllocation::class;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        return $this->behaviorCors($behaviors);
    }

    protected function behaviorAccess($behaviors)
    {
        // setup access
        $behaviors['access'] = [
            'class' => AccessControl::class,
            'only'  => ['index', 'view'],
            'rules' => [
                [
                    'allow'   => true,
                    'actions' => ['index', 'view'],
                    'roles'   => ['admin', 'staffProv', 'staffKec', 'staffKel'],
                ],
            ],
        ];

        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();

        // Override Actions
        unset($actions['view']);
        unset($actions['delete']);

        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

        return $actions;
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
    }

    /**
     * Checks the privilege of the current user.
     * throw ForbiddenHttpException if access should be denied
     *
     * @param  string  $action  the ID of the action to be executed
     * @param  object  $model  the model to be accessed. If null, it means no specific model is being accessed.
     * @param  array  $params  additional parameters
     */
    public function checkAccess($action, $model = null, $params = [])
    {
    }

    /**
     * @param $id
     * @return mixed|\app\models\Beneficiery
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionView($id)
    {
        $model = $this->findModel($id, $this->modelClass);

        return $model;
    }

    public function prepareDataProvider()
    {
        $params = Yii::$app->request->getQueryParams();

        $user          = Yii::$app->user;
        $authUserModel = $user->identity;

        $search = new BeneficiaryAllocationSearch();

        return $search->search($params);
    }
}
