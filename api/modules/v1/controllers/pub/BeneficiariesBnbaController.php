<?php

namespace app\modules\v1\controllers\pub;

use app\models\pub\BeneficiaryBnba;
use app\models\pub\BeneficiaryBnbaSearch;
use Illuminate\Support\Arr;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\NotFoundHttpException;
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

        if (empty($params)) {
            $search = (new \yii\db\Query())
                    ->from('beneficiaries_bnba_statistic_type')
                    ->all();
        } else {
            $search = new BeneficiaryBnbaSearch();
            $search = $search->getStatisticsByType($params);
        }

        // Reformat result
        $beneficiaryTypes = [
            '1' => Yii::t('app', 'type.beneficiaries.pkh'),
            '2' => Yii::t('app', 'type.beneficiaries.bnpt'),
            '3' => Yii::t('app', 'type.beneficiaries.bnpt_perluasan'),
            '4' => Yii::t('app', 'type.beneficiaries.bansos_tunai'),
            '5' => Yii::t('app', 'type.beneficiaries.bansos_presiden_sembako'),
            '6' => Yii::t('app', 'type.beneficiaries.bansos_provinsi'),
            '8' => Yii::t('app', 'type.beneficiaries.bansos_kabkota'),
            '7' => Yii::t('app', 'type.beneficiaries.dana_desa'),
        ];

        $data = [];
        foreach ($beneficiaryTypes as $key => $val) {
            $data[$val]['source'] = $this->setSourceBeneficiaries($key);
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

    protected function setSourceBeneficiaries($key)
    {
        $sourceBeneficiaries = '';
        if (in_array($key, [1, 2, 3, 4, 5])) {
            $sourceBeneficiaries = Yii::t('app', 'source.beneficiaries.kemensos');
        } elseif ($key == 6) {
            $sourceBeneficiaries = Yii::t('app', 'source.beneficiaries.apbdpemprovjabar');
        } elseif ($key == 7) {
            $sourceBeneficiaries = Yii::t('app', 'source.beneficiaries.kemendes');
        } elseif ($key == 8) {
            $sourceBeneficiaries = Yii::t('app', 'source.beneficiaries.apbdkotajabar');
        }

        return $sourceBeneficiaries;
    }

    /**
     * @return mixed|\app\models\pub\Beneficieries
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionStatisticsByArea()
    {
        $publicBaseUrl = Yii::$app->params['storagePublicBaseUrl'];
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


        if ($params['area_type'] == 'kode_kab') {
            $search = (new \yii\db\Query())
                    ->from('beneficiaries_bnba_statistic_area')
                    ->all();
        } else {
            $search = new BeneficiaryBnbaSearch();
            $search = $search->getStatisticsByArea($params);
        }

        if ($codeBps == null) {
            foreach ($search as $key => $val) {
                $areaName = $val[$params['area_type']] != null ? 'RW ' . $val[$params['area_type']] : Yii::t('app', 'beneficiaries.incomplete_address');
                $data[$key] = [
                    'name' => $areaName,
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
            ->orderBy('name asc')
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

    /**
     * @return mixed|\app\models\pub\Beneficieries
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionStatisticsUpdate()
    {
        $params = Yii::$app->request->getQueryParams();

        if (empty($params)) {
            // throw new NotFoundHttpException("Object not found: $id");
            throw new NotFoundHttpException("Object not found");


        }

        // Update statistic calculation by Area kabkota
        $searchType = new BeneficiaryBnbaSearch();
        $statisticByType = $searchType->getStatisticsByType($params);

        $rowsType = [];
        foreach ($statisticByType as $key => $val) {
            $rowsType[] = [
                'id_tipe_bansos' => $val['id_tipe_bansos'],
                'is_dtks' => $val['is_dtks'],
                'total' => $val['total'],
            ];
        }

        if (count($rowsType) > 0) {
            Yii::$app->db->createCommand()->truncateTable('beneficiaries_bnba_statistic_type')->execute();
            Yii::$app->db->createCommand()->batchInsert('beneficiaries_bnba_statistic_type', [
                'id_tipe_bansos',
                'is_dtks',
                'total'
            ], $rowsType)->execute();
        }

        // Update statistic calculation by Area kabkota
        $searchArea = new BeneficiaryBnbaSearch();
        $params['area_type'] = 'kode_kab';
        $statisticByArea = $searchArea->getStatisticsByArea($params);

        $rowsArea = [];
        foreach ($statisticByArea as $key => $val) {
            $rowsArea[] = [
                'kode_kab' => $val['kode_kab'],
                'total' => $val['total'],
            ];
        }

        if (count($rowsArea) > 0) {
            Yii::$app->db->createCommand()->truncateTable('beneficiaries_bnba_statistic_area')->execute();
            Yii::$app->db->createCommand()->batchInsert('beneficiaries_bnba_statistic_area', [
                'kode_kab',
                'total'
            ], $rowsArea)->execute();
        }

        $rowsType = (new Query())
            ->from('beneficiaries_bnba_statistic_type')
            ->select('id_tipe_bansos, is_dtks, total')
            ->all();

        $rowsArea = (new Query())
            ->from('beneficiaries_bnba_statistic_area')
            ->select('kode_kab, total')
            ->all();

        $data['statistics_by_type'] = $rowsType;
        $data['statistics_by_area'] = $rowsArea;

        return $data;
    }

    public function prepareDataProvider()
    {
        $params = Yii::$app->request->getQueryParams();

        $search = new BeneficiaryBnbaSearch();

        return $search->search($params);
    }
}
