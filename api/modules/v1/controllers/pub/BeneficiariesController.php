<?php

namespace app\modules\v1\controllers\pub;

use app\models\pub\Beneficiary;
use app\models\pub\BeneficiarySearch;
use Illuminate\Support\Arr;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\modules\v1\controllers\ActiveController as ActiveController;

/**
 * BeneficiaryController implements the CRUD actions for Beneficiary model.
 */
class BeneficiariesController extends ActiveController
{
    public $modelClass = Beneficiary::class;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        return $this->behaviorCors($behaviors);
    }

    protected function behaviorAccess($behaviors)
    {
        $behaviors['authenticator']['except'] = [
            'index', 'view', 'summary'
        ];

        // setup access
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => ['index', 'view'],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['index', 'view', 'summary'],
                    'roles' => ['?'],

                ]
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
     * @param $id
     * @return mixed|\app\models\pub\Beneficieries
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionView($id)
    {
        $model = $this->findModel($id, $this->modelClass);
        return $model;
    }

    /**
     * @param $id
     * @return mixed|\app\models\pub\Beneficieries
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionSummary()
    {
        $params = Yii::$app->request->getQueryParams();

        $search = new BeneficiarySearch();
        $search->tahap = Arr::get($params, 'tahap');

        $result = $search->validate();
        if ($result === false) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(422);
            return $search->getErrors();
        }

        return $search->getSummaryStatusVerification($params);
    }

    public function prepareDataProvider()
    {
        $params = Yii::$app->request->getQueryParams();

        $search = new BeneficiarySearch();
        $search->tahap = Arr::get($params, 'tahap');

        $result = $search->validate();
        if ($result === false) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(422);
            return $search->getErrors();
        }

        return $search->search($params);
    }
}
