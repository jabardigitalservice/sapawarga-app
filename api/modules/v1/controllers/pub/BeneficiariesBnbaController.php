<?php

namespace app\modules\v1\controllers\pub;

use app\models\pub\BeneficiaryBnba;
use app\models\pub\BeneficiaryBnbaSearch;
use Illuminate\Support\Arr;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use app\modules\v1\controllers\ActiveController as ActiveController;

/**
 * BeneficiariesBnbaController implements the CRUD actions for BeneficiaryBnba model.
 */
class BeneficiariesBnbaController extends ActiveController
{
    public $modelClass = BeneficiaryBnba::class;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        return $this->behaviorCors($behaviors);
    }

    protected function behaviorAccess($behaviors)
    {
        $behaviors['authenticator']['except'] = [
            'index', 'view'
        ];

        // setup access
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => ['index', 'view'],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['index', 'view'],
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

    public function prepareDataProvider()
    {
        $params = Yii::$app->request->getQueryParams();

        $search = new BeneficiaryBnbaSearch();

        return $search->search($params);
    }
}
