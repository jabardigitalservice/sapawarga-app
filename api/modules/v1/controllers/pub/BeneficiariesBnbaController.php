<?php

namespace app\modules\v1\controllers\pub;

use app\models\pub\BeneficiaryBnba;
use app\models\pub\BeneficiaryBnbaSearch;
use app\components\BeneficiaryHelper;
use Illuminate\Support\Arr;
use Yii;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\base\DynamicModel;
use yii\web\NotFoundHttpException;
use Illuminate\Support\Collection;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use app\modules\v1\controllers\ActiveController as ActiveController;

/**
 * BeneficiariesBnbaController implements the CRUD actions for BeneficiaryBnba model.
 */
class BeneficiariesBnbaController extends ActiveController
{
    public const REDIS_KEY_BNBA_TYPE = 'bnba-statisticsbytype-';
    public const REDIS_KEY_BNBA_AREA = 'bnba-statisticsbyarea-';

    public $modelClass = BeneficiaryBnba::class;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        return $this->behaviorCors($behaviors);
    }

    protected function behaviorAccess($behaviors)
    {
        $behaviors['authenticator']['except'] = [
            'index', 'view', 'statistics-by-type', 'statistics-by-area', 'statistics-update', 'flagging', 'tracking'
        ];

        // setup access
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => ['index', 'view'],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['index', 'view', 'statistics-by-type', 'statistics-by-area', 'statistics-update', 'flagging', 'tracking'],
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
        $type = Arr::get($params, 'type');
        $tahap = Arr::get($params, 'tahap', 1);

        // $type = provinsi means API call from homepage
        if ($type == 'provinsi') {
            $search = (new \yii\db\Query())
                    ->from('beneficiaries_bnba_statistic_type')
                    ->where(['tahap_bantuan' => $tahap])
                    ->all();
        } else {
            $cache = Yii::$app->cache;
            $key = self::REDIS_KEY_BNBA_TYPE . implode($params);
            $search = $cache->get($key);
            if (! $search) {
                $search = new BeneficiaryBnbaSearch();
                $search = $search->getStatisticsByType($params);
                $cache->set($key, $search);
            }
        }

        // Reformat result
        $data = [];
        foreach (BeneficiaryHelper::getBansosTypeList() as $key => $val) {
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
        if ($key < 6) {
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
        $publicBaseUrl = Yii::$app->params['storagePublicBaseUrl'] . '/logo/';
        $params = Yii::$app->request->getQueryParams();
        $tahap = Arr::get($params, 'tahap', 1);
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
                    ->where(['tahap_bantuan' => $tahap])
                    ->all();
        } else {
            $cache = Yii::$app->cache;
            $key = self::REDIS_KEY_BNBA_AREA . implode($params);
            $search = $cache->get($key);
            if (! $search) {
                $search = new BeneficiaryBnbaSearch();
                $search = $search->getStatisticsByArea($params);
                $cache->set($key, $search);
            }
        }

        // Reformat for RW area
        if ($codeBps == null) {
            foreach ($search as $key => $val) {
                $areaName = $val['area'] != null ? 'RW ' . $val['area'] : Yii::t('app', 'beneficiaries.incomplete_address');
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
                $data[$key]['image'] = $publicBaseUrl . $area['code_bps'] . '.png';
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

    /**
     * @return mixed|\app\models\pub\Beneficieries
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionStatisticsUpdate()
    {
        $params = Yii::$app->request->getQueryParams();
        $tahap = Arr::get($params, 'tahap');

        if (empty($tahap)) {
            throw new NotFoundHttpException('Object not found');
        }

        $updateStatisticsByType = $this->updateStatisticsByType($params);
        $updateStatisticsByArea = $this->updateStatisticsByArea($params);

        $data['statistics_by_type'] = $updateStatisticsByType;
        $data['statistics_by_area'] = $updateStatisticsByArea;

        return $data;
    }

    private function updateStatisticsByType($params)
    {
        $searchType = new BeneficiaryBnbaSearch();
        $statisticByType = $searchType->getStatisticsByType($params);
        $tahap = Arr::get($params, 'tahap');

        $rowsType = [];
        foreach ($statisticByType as $key => $val) {
            $rowsType[] = [
                'id_tipe_bansos' => $val['id_tipe_bansos'],
                'is_dtks' => $val['is_dtks'],
                'total' => $val['total'],
                'tahap_bantuan' => $tahap,
            ];
        }

        if (count($rowsType) > 0) {
            Yii::$app->db->createCommand()->delete('beneficiaries_bnba_statistic_type', ['tahap_bantuan' => $tahap])->execute();
            Yii::$app->db->createCommand()->batchInsert('beneficiaries_bnba_statistic_type', [
                'id_tipe_bansos',
                'is_dtks',
                'total',
                'tahap_bantuan',
            ], $rowsType)->execute();
        }

        return $rowsType;
    }

    private function updateStatisticsByArea($params)
    {
        $searchArea = new BeneficiaryBnbaSearch();
        $params['area_type'] = 'kode_kab';
        $statisticByArea = $searchArea->getStatisticsByArea($params);

        $tahap = Arr::get($params, 'tahap');
        $rowsArea = [];
        foreach ($statisticByArea as $key => $val) {
            $rowsArea[] = [
                'kode_kab' => $val['kode_kab'],
                'total' => $val['total'],
                'tahap_bantuan' => $tahap,
            ];
        }

        if (count($rowsArea) > 0) {
            Yii::$app->db->createCommand()->delete('beneficiaries_bnba_statistic_area', ['tahap_bantuan' => $tahap])->execute();
            Yii::$app->db->createCommand()->batchInsert('beneficiaries_bnba_statistic_area', [
                'kode_kab',
                'total',
                'tahap_bantuan',
            ], $rowsArea)->execute();
        }

        return $rowsArea;
    }

    /**
     * @param $id
     * @return mixed|\app\models\pub\Beneficieries
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionFlagging()
    {
        $params = Yii::$app->request->getQueryParams();

        $nik = Arr::get($params, 'nik');
        $tahap = Arr::get($params, 'tahap');
        $from = Arr::get($params, 'from');

        $nikModel = new DynamicModel(['nik' => $nik]);
        $nikModel->addRule('nik', 'trim');
        $nikModel->addRule('nik', 'required');

        if ($nikModel->validate() === false) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(422);

            return $nikModel->getErrors();
        }

        $client = new Client(['base_uri' => getenv('BANSOS_API_BASE_URL')]);
        $response = $client->get('non_dtks/flagging/rts', [
            'query' => [
                'api_key' => getenv('BANSOS_API_KEY'),
                'nik' => $nik,
                'tahap' => $tahap,
            ]
        ]);

        $response = json_decode($response->getBody(), true);

        // Masking some data
        if (count($response['data'])) {
            foreach ($response['data'] as $key => $value) {
                $response['data'][$key]['nik'] = ($from == 'mobile') ? $value['nik'] : BeneficiaryHelper::getNikMasking($value['nik']);
                $response['data'][$key]['no_kk'] = BeneficiaryHelper::getKkMasking($value['no_kk']);
                $response['data'][$key]['nama_krt'] = BeneficiaryHelper::getNameMasking($value['nama_krt']);
                $response['data'][$key]['alamat'] = BeneficiaryHelper::getNameMasking($value['alamat']);
            }
        }

        return $response['data'];
    }

    /**
     * @return mixed|\app\models\pub\Beneficieries
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionTracking()
    {
        $params = Yii::$app->request->getQueryParams();

        $nik = Arr::get($params, 'nik');

        $nikModel = new DynamicModel(['nik' => $nik]);
        $nikModel->addRule('nik', 'trim');
        $nikModel->addRule('nik', 'required');

        if ($nikModel->validate() === false) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(422);

            return $nikModel->getErrors();
        }

        $client = new Client(['base_uri' => getenv('BANSOS_API_BASE_URL')]);

        try {
            $response = $client->get('tracking/' . $nik, ['headers' => ['x-api-key' => getenv('BANSOS_TRACKING_API_KEY'),]]);
        } catch (RequestException $e) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(503);

            return 'Error Private API';
        }

        $response = json_decode($response->getBody(), true);

        return $response['data'];
    }
}
