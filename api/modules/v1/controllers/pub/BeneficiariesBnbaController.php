<?php

namespace app\modules\v1\controllers\pub;

use app\models\pub\BeneficiaryBnba;
use app\models\pub\BeneficiaryBnbaSearch;
use Illuminate\Support\Arr;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use Illuminate\Support\Collection;
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
        $searchedModel = $this->modelClass::find()
            ->where(['id' => $id])
            ->andWhere(['is_deleted' => null])
            ->one();

        if ($searchedModel === null) {
            throw new NotFoundHttpException("Object not found: $id");
        }

        return $searchedModel;
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

        $data = [];
        foreach ($beneficiaryTypes as $key => $val) {
            $data[$val]['non-dtks'] = 0;
            $data[$val]['dtks'] = 0;
            $data[$val]['total'] = 0;
            foreach ($search as $value) {
                if ($key == $value['id_tipe_bansos']) {
                    if (! $value['is_dtks']) {
                        $data[$val]['non-dtks'] = isset($value['total']) ? intval($value['total']) : 0;
                    } else {
                        $data[$val]['dtks'] = isset($value['total']) ? intval($value['total']) : 0;
                    }
                    $data[$val]['total'] += intval($value['total']);
                }
            }
        }

        return $data;
    }

    /**
     * @return mixed|\app\models\pub\Beneficieries
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionStatisticsByArea()
    {
        $params = Yii::$app->request->getQueryParams();
        $data = [];

        $params['area_type'] = 'kode_kab';
        $codeBps = 32;
        if (Arr::get($params, 'type') == 'kabkota') {
            $params['area_type'] = 'kode_kec';
            $codeBps = $params['kabkota_bps_id'];
        } elseif (Arr::get($params, 'type') == 'kec') {
            $params['area_type'] = 'kode_kel';
            $codeBps = $params['kec_bps_id'];
        } elseif (Arr::get($params, 'type') == 'kel') {
            $params['area_type'] = 'RW';
            $codeBps = null;
        }

        $search = new BeneficiaryBnbaSearch();
        $search = $search->getStatisticsByArea($params);

        if ($codeBps == null) {
            foreach ($search as $key => $val) {
                $area = $val[$params['area_type']] != null ? $val[$params['area_type']] : 'Tidak Terdaftar';
                $data[$key] = [
                    'name' => $params['area_type'] . ' ' . $area,
                    'total' => $val['total']
                ];
            }
            return $data;
        }

        // Reformat result by areas
        $areas = (new \yii\db\Query())
            ->select(['code_bps', 'name'])
            ->from('areas')
            ->where(['=','code_bps_parent', $codeBps])
            ->createCommand()
            ->queryAll();
        $areas = new Collection($areas);

        $search = Arr::pluck($search, 'total', $params['area_type']);

        foreach ($areas as $key => $area) {
            $data[$key] = [
                'code_bps' => $area['code_bps'],
                'name' => $area['name'],
                'total' => isset($search[$area['code_bps']]) ? intval($search[$area['code_bps']]) : 0
            ];

            if ($params['area_type'] == 'kode_kab') {
                $data[$key]['image'] = $area['code_bps'] . '.svg';
            }
        }

        return $data;
    }

    public function prepareDataProvider()
    {
        $params = Yii::$app->request->getQueryParams();

        $search = new BeneficiaryBnbaSearch();

        return $search->search($params);
    }
}
