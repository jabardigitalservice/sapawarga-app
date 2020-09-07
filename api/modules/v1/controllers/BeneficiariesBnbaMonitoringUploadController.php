<?php

namespace app\modules\v1\controllers;

use app\models\BeneficiaryBnbaMonitoringUpload;
use app\models\BeneficiaryBnbaMonitoringUploadSearch;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;

/**
 * BeneficiariesBnbaTahapSatuController implements the CRUD actions for BeneficiaryBnbaMonitoringUpload model.
 */
class BeneficiariesBnbaMonitoringUploadController extends ActiveController
{
    public $modelClass = BeneficiaryBnbaMonitoringUpload::class;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        return $this->behaviorCors($behaviors);
    }

    protected function behaviorAccess($behaviors)
    {
        // setup access
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => ['index', 'update-data'],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['index', 'update-data'],
                    'roles' => ['admin', 'staffProv'],
                ],
            ],
        ];

        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();

        // Override Actions
        unset($actions['create']);
        unset($actions['update']);
        unset($actions['delete']);

        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

        return $actions;
    }

    public function prepareDataProvider()
    {
        $params = Yii::$app->request->getQueryParams();
        $search = new BeneficiaryBnbaMonitoringUploadSearch();

        return $search->search($params);
    }

    public function actionUpdateData()
    {
        BeneficiaryBnbaMonitoringUpload::updateData();
        return 'success';
    }

}
