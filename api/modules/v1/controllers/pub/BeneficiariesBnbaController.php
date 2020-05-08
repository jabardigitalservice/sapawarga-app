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
            'index', 'view', 'statistics-by-type', 'statistics-by-area'
        ];

        // setup access
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => ['index', 'view'],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['index', 'view', 'statistics-by-type', 'statistics-by-area'],
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
     * @return mixed|\app\models\pub\Beneficieries
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionStatisticsByType()
    {
        $params = Yii::$app->request->getQueryParams();

        $search = new BeneficiaryBnbaSearch();
        $search = $search->getStatisticsByType($params);

        // Reformat result
        $beneficiaryTypes = [
            '1' => Yii::t('app', 'type.beneficiaries.pkh'),
            '2' => Yii::t('app', 'type.beneficiaries.bnpt'),
            '3' => Yii::t('app', 'type.beneficiaries.bnpt perluasan'),
            '4' => Yii::t('app', 'type.beneficiaries.bansos tunai'),
            '5' => Yii::t('app', 'type.beneficiaries.bansos presiden sembako'),
            '6' => Yii::t('app', 'type.beneficiaries.bansos provinsi'),
            '7' => Yii::t('app', 'type.beneficiaries.dana desa'),
        ];

        $jml = Arr::pluck($search, 'total', 'id_tipe_bansos');

        $data = [];
        $total = 0;
        foreach ($beneficiaryTypes as $key => $val) {
            $data[$val] = isset($jml[$key]) ? intval($jml[$key]) : 0;
            $total += $data[$val];
        }
        $data['total'] = $total;

        return $data;
    }

    /**
     * @return mixed|\app\models\pub\Beneficieries
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionStatisticsByArea()
    {
    }

    public function prepareDataProvider()
    {
        $params = Yii::$app->request->getQueryParams();

        $search = new BeneficiaryBnbaSearch();

        return $search->search($params);
    }
}
