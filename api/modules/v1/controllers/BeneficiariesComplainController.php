<?php

namespace app\modules\v1\controllers;

use app\models\Area;
use app\models\BeneficiaryComplain;
use app\models\BeneficiaryComplainSearch;
use Yii;
use yii\filters\AccessControl;

/**
 * BeneficiariesComplainController implements the CRUD actions for BeneficiaryComplain model.
 */
class BeneficiariesComplainController extends ActiveController
{
    public $modelClass = BeneficiaryComplain::class;

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
                    'roles'   => ['admin', 'staffProv', 'staffKabkota', 'staffKec', 'staffKel'],
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

        if ($user->can('staffKabkota')) {
            $area = Area::find()->where(['id' => $authUserModel->kabkota_id])->one();
            $params['kode_kab'] = $area->code_bps;
        } elseif ($user->can('staffKec')) {
            $area = Area::find()->where(['id' => $authUserModel->kec_id])->one();
            $params['kode_kec'] = $area->code_bps;
        } elseif ($user->can('staffKel') || $user->can('staffRW') || $user->can('trainer')) {
            $area = Area::find()->where(['id' => $authUserModel->kel_id])->one();
            $params['kode_kel'] = $area->code_bps;
            $params['rw'] = $authUserModel->rw;
        }

        $search = new BeneficiaryComplainSearch();

        return $search->search($params);
    }
}
