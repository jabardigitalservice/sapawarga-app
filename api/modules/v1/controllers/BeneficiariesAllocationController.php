<?php

namespace app\modules\v1\controllers;

use Yii;
use app\models\Area;
use app\models\Beneficiary;
use app\models\BeneficiarySearch;
use yii\filters\AccessControl;

/**
 * BeneficiariesAllocationController implements the CRUD actions for Beneficiary model.
 */
class BeneficiariesAllocationController extends ActiveController
{
    public $modelClass = Beneficiary::class;

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

        $user = Yii::$app->user;
        $authUserModel = $user->identity;

        $search = new BeneficiarySearch();
        $search->userRole = $authUserModel->role;

        if ($user->can('staffKel') || $user->can('staffRW') || $user->can('trainer')) {
            // Get bps id
            $area = Area::find()->where(['id' => $authUserModel->kel_id])->one();

            $search->scenario = BeneficiarySearch::SCENARIO_LIST_USER;
            $params['domicile_kel_bps_id'] = $area->code_bps;
            $params['domicile_rw'] = $authUserModel->rw;
        }

        return $search->search($params);
    }
}
