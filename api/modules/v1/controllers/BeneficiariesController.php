<?php

namespace app\modules\v1\controllers;

use app\components\BeneficiaryHelper;
use app\components\ModelHelper;
use app\models\Area;
use app\models\Beneficiary;
use app\models\beneficiary\BeneficiaryApproval;
use app\models\BeneficiarySearch;
use app\models\User;
use app\validator\NikRateLimitValidator;
use app\validator\NikValidator;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Yii;
use yii\base\DynamicModel;
use yii\filters\AccessControl;
use yii\web\BadRequestHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\web\ServerErrorHttpException;
use Illuminate\Support\Collection;
use Illuminate\Support\Arr;

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
        // add optional authentication for public endpoints
        $behaviors['authenticator']['optional'] = ['current-tahap'];

        // setup access
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => ['index', 'view', 'create', 'update', 'delete', 'check-nik', 'check-kk', 'check-address', 'dashboard-list', 'dashboard-summary', 'approval', 'bulk-approval', 'current-tahap'],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['index', 'view', 'create', 'update', 'delete', 'check-nik', 'check-kk', 'check-address', 'dashboard-list', 'dashboard-summary', 'current-tahap'],
                    'roles' => ['admin', 'staffProv', 'staffKabkota', 'staffKec', 'staffKel', 'staffRW', 'trainer'],
                ],
                [
                    'allow' => true,
                    'actions' => ['approval', 'bulk-approval', 'dashboard-approval'],
                    'roles' => ['admin', 'staffKabkota', 'staffKec', 'staffKel'],
                ],
                [
                    'allow' => true,
                    'actions' => ['current-tahap'],
                    'roles' => ['?'],
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
        unset($actions['view']);
        unset($actions['update']);
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
     * @param string $action the ID of the action to be executed
     * @param object $model the model to be accessed. If null, it means no specific model is being accessed.
     * @param array $params additional parameters
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

    public function actionCreate()
    {
        $model = new Beneficiary();

        $model->load(Yii::$app->getRequest()->getBodyParams(), '');

        if ($model->validate() && $model->save()) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(201);
        } else {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(422);

            return $model->getErrors();
        }

        return $model;
    }

    public function actionUpdate($id)
    {
        $model = $this->findModel($id, Beneficiary::class);
        $params = Yii::$app->getRequest()->getBodyParams();

        $model->load($params, '');

        if ($model->validate() && $model->save()) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(200);
        } else {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(422);

            return $model->getErrors();
        }

        return $model;
    }

    public function prepareDataProvider()
    {
        $params = Yii::$app->request->getQueryParams();
        $params = array_merge($params, $this->getAreaByUser());

        $user = Yii::$app->user;
        $authUserModel = $user->identity;

        $search = new BeneficiarySearch();
        $search->userRole = $authUserModel->role;
        $search->tahap = Arr::get($params, 'tahap');

        //assign search scenario based on role
        if ($user->can('staffRW') || $user->can('trainer')) {
            $search->scenario = BeneficiarySearch::SCENARIO_LIST_USER;
        } else {
            $search->scenario = BeneficiarySearch::SCENARIO_LIST_STAFF;
        }

        $result = $search->validate();
        if ($result === false) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(422);
            return $search->getErrors();
        }

        return $search->search($params);
    }

    /**
     * @return array
     */
    public function actionCheckNik()
    {
        $model = new Beneficiary();
        $model->scenario = Beneficiary::SCENARIO_VALIDATE_NIK;
        $model->load(Yii::$app->request->getQueryParams(), '');

        $result = $model->validate();
        if ($result === false) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(422);
            return $model->getErrors();
        }

        return $this->actionNik($model->nik);
    }

    /**
     * @param $id
     * @return array
     */
    public function actionCheckKk()
    {
        $model = new Beneficiary();
        $model->scenario = Beneficiary::SCENARIO_VALIDATE_KK;
        $model->load(Yii::$app->request->getQueryParams(), '');

        $result = $model->validate();
        if ($result === false) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(422);
            return $model->getErrors();
        }

        return 'ok';
    }

    /**
     * @param $id
     * @return array
     */
    public function actionCheckAddress()
    {
        $model = new Beneficiary();
        $model->scenario = Beneficiary::SCENARIO_VALIDATE_ADDRESS;
        $model->load(Yii::$app->request->getQueryParams(), '');

        $result = $model->validate();
        if ($result === false) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(422);
            return $model->getErrors();
        }

        return $result;
    }

    /**
     * @param $nik
     * @throws \yii\web\HttpException
     * @throws \yii\web\NotFoundHttpException
     */
    public function actionNik($nik)
    {
        /**
         * $status 0 = format NIK tidak valid
         * $status 1 = format NIK valid, tapi gagal cek ke DWH
         * $status 2 = format NIK valid, tidak ditemukan di DWH
         * $status 3 = format NIK valid, ditemukan di DWH
         * $status 4 = format NIK valid, over quota di DWH
         */
        $user      = Yii::$app->user;
        $userModel = $user->identity;
        $ipAddress = Yii::$app->request->userIP;

        $nikModel = new DynamicModel(['nik' => $nik, 'user_id' => $user->id]);
        $nikModel->addRule('nik', 'trim');
        $nikModel->addRule('nik', 'required');
        $nikModel->addRule('nik', NikValidator::class);
        $nikModel->addRule('nik', NikRateLimitValidator::class);

        $log = [
            'user_id'    => $user->id,
            'nik'        => $nik,
            'ip_address' => $ipAddress,
            'status'     => 0,
            'created_at' => time(),
            'updated_at' => time(),
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ];

        if ($nikModel->validate() === false) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(422);

            Yii::$app->db->createCommand()->insert('beneficiaries_nik_logs', $log)->execute();

            return $nikModel->getErrors();
        }

        $client = new Client([
            'base_uri' => getenv('KEPENDUDUKAN_API_BASE_URL'),
            'timeout'  => 15.00,
        ]);

        $requestBody = [
            'http_errors' => false,
            'json' => [
                'user_id'   => "{$userModel->username}@sapawarga",
                'api_key'   => getenv('KEPENDUDUKAN_API_KEY'),
                'event_key' => 'cek_bansos',
                'nik'       => $nik,
            ],
        ];

        try {
            $response = $client->post('kependudukan/nik', $requestBody);
        } catch (RequestException $e) {
            $log['status'] = 1;
            Yii::$app->db->createCommand()->insert('beneficiaries_nik_logs', $log)->execute();
            return 'Error Private API';
        }

        if ($response->getStatusCode() <> 200) {
            $log['status'] = 1;

            Yii::$app->db->createCommand()->insert('beneficiaries_nik_logs', $log)->execute();

            return 'Error Private API';
        }

        $responseBody    = json_decode($response->getBody(), true);

        $contentResponse = $responseBody['data']['content'];
        $dwhResponse     = $responseBody['data']['dwh_response'];

        if (isset($dwhResponse['response_code']) && $dwhResponse['response_code'] === '02') {
            $log['status'] = 2;
        }

        if (isset($dwhResponse['response_code']) && $dwhResponse['response_code'] === '05') {
            $log['status'] = 4;
        }

        if (isset($dwhResponse['content'])) {
            $log['status'] = 3;
        }

        Yii::$app->db->createCommand()->insert('beneficiaries_nik_logs', $log)->execute();

        return 'ok';
    }

    /* VERVAL DASHBOARD - SUMMARY */

    protected function getDashboardSummaryQuery($conditionals)
    {
        $params = Yii::$app->request->getQueryParams();
        $statusVerificationColumn = BeneficiaryHelper::getStatusVerificationColumn(Arr::get($params, 'tahap'));

        $query = (new \yii\db\Query())
            ->select([$statusVerificationColumn, 'COUNT(*) AS jumlah'])
            ->from('beneficiaries')
            ->where(['=', 'status', Beneficiary::STATUS_ACTIVE]);
        foreach ($conditionals as $conditional) {
            $query = $query->andWhere($conditional);
        }
        $query = $query->groupBy([$statusVerificationColumn])
            ->createCommand()
            ->queryAll();

        return $query;
    }

    protected function transformCount($lists, $statusVerificationColumn)
    {
        $status_maps = [
            '1' => 'pending',
            '2' => 'rejected',
            '3' => 'approved',
            '4' => 'rejected_kel',
            '5' => 'approved_kel',
            '6' => 'rejected_kec',
            '7' => 'approved_kec',
            '8' => 'rejected_kabkota',
            '9' => 'approved_kabkota',
        ];
        $data = [];
        $jml = $lists->pluck('jumlah', $statusVerificationColumn);
        $total = 0;
        foreach ($status_maps as $key => $map) {
            $data[$map] = isset($jml[$key]) ? intval($jml[$key]) : 0;
            $total += $data[$map];
        }
        $data['total'] = $total;
        return $data;
    }

    protected function getDashboardSummaryData($conditionals)
    {
        $params = Yii::$app->request->getQueryParams();
        $statusVerificationColumn = BeneficiaryHelper::getStatusVerificationColumn(Arr::get($params, 'tahap'));

        $counts = $this->getDashboardSummaryQuery($conditionals);
        $counts = new Collection($counts);
        $counts = $this->transformCount($counts, $statusVerificationColumn);

        return $counts;
    }

    public function actionDashboardSummary()
    {
        $params = Yii::$app->request->getQueryParams();

        $type = Arr::get($params, 'type');
        $code_bps = Arr::get($params, 'code_bps');
        $rw = Arr::get($params, 'rw');

        switch ($type) {
            case 'provinsi':
                $counts = $this->getDashboardSummaryData([]);
                $counts_baru = $this->getDashboardSummaryData([['<>', 'created_by', 2]]);
                break;
            case 'kabkota':
                $counts = $this->getDashboardSummaryData([['=', 'domicile_kabkota_bps_id', $code_bps]]);
                $counts_baru = $this->getDashboardSummaryData([
                    ['=', 'domicile_kabkota_bps_id', $code_bps],
                    ['<>', 'created_by', 2],
                ]);
                break;
            case 'kec':
                $counts = $this->getDashboardSummaryData([
                    ['=', 'domicile_kabkota_bps_id', substr($code_bps, 0, 4)],
                    ['=', 'domicile_kec_bps_id', $code_bps],
                ]);
                $counts_baru = $this->getDashboardSummaryData([
                    ['=', 'domicile_kabkota_bps_id', substr($code_bps, 0, 4)],
                    ['=', 'domicile_kec_bps_id', $code_bps],
                    ['<>', 'created_by', 2],
                ]);
                break;
            case 'kel':
                $counts = $this->getDashboardSummaryData([
                    ['=', 'domicile_kabkota_bps_id', substr($code_bps, 0, 4)],
                    ['=', 'domicile_kec_bps_id', substr($code_bps, 0, 7)],
                    ['=', 'domicile_kel_bps_id', $code_bps],
                ]);
                $counts_baru = $this->getDashboardSummaryData([
                    ['=', 'domicile_kabkota_bps_id', substr($code_bps, 0, 4)],
                    ['=', 'domicile_kec_bps_id', substr($code_bps, 0, 7)],
                    ['=', 'domicile_kel_bps_id', $code_bps],
                    ['<>', 'created_by', 2],
                ]);
                break;
            case 'rw':
                $counts = $this->getDashboardSummaryData([
                    ['=', 'domicile_kabkota_bps_id', substr($code_bps, 0, 4)],
                    ['=', 'domicile_kec_bps_id', substr($code_bps, 0, 7)],
                    ['=', 'domicile_kel_bps_id', $code_bps],
                    ['=', 'domicile_rw', $rw],
                ]);
                $counts_baru = $this->getDashboardSummaryData([
                    ['=', 'domicile_kabkota_bps_id', substr($code_bps, 0, 4)],
                    ['=', 'domicile_kec_bps_id', substr($code_bps, 0, 7)],
                    ['=', 'domicile_kel_bps_id', $code_bps],
                    ['=', 'domicile_rw', $rw],
                    ['<>', 'created_by', 2],
                ]);
                break;
        }
        $counts['baru'] = $counts_baru;
        return $counts;
    }

    /* VERVAL DASHBOARD - LIST */

    protected function getDashboardListQuery($areaColumn, $conditionals, $orderBy)
    {
        $params = Yii::$app->request->getQueryParams();
        $statusVerificationColumn = BeneficiaryHelper::getStatusVerificationColumn(Arr::get($params, 'tahap'));

        // base query
        $query = (new \yii\db\Query())
            ->select([$areaColumn, $statusVerificationColumn, 'COUNT(*) AS jumlah'])
            ->from('beneficiaries')
            ->where(['=', 'status', Beneficiary::STATUS_ACTIVE]);
        // conditionals
        foreach ($conditionals as $conditional) {
            $query = $query->andWhere($conditional);
        }
        // group and order
        $query = $query->groupBy([$areaColumn, $statusVerificationColumn]);
        if ($orderBy) {
            $query = $query->orderBy($orderBy);
        }
        // execute query
        $query = $query->createCommand()->queryAll();

        return $query;
    }

    protected function getDashboardListData ($areaColumn, $conditionals, $orderBy) {
        $params = Yii::$app->request->getQueryParams();
        $statusVerificationColumn = BeneficiaryHelper::getStatusVerificationColumn(Arr::get($params, 'tahap'));

        $transformCount = function ($lists) use ($statusVerificationColumn) {
            return $this->transformCount($lists, $statusVerificationColumn);
        };

        $counts = $this->getDashboardListQuery($areaColumn, $conditionals, $orderBy);
        // group by Collection keys
        $counts = new Collection($counts);
        $counts = $counts->groupBy($areaColumn);
        $counts->transform($transformCount);

        return $counts;
    }

    public function actionDashboardList()
    {
        $params = Yii::$app->request->getQueryParams();

        $type = Arr::get($params, 'type');
        $code_bps = Arr::get($params, 'code_bps');
        $rw = Arr::get($params, 'rw');

        $getChildAreas = function ($parentCodeBps) {
            return (new \yii\db\Query())
                ->select(['code_bps', 'name'])
                ->from('areas')
                ->where(['=', 'code_bps_parent', $parentCodeBps])
                ->createCommand()
                ->queryAll();
        };

        switch ($type) {
            case 'provinsi':
                $areas = $getChildAreas('32');
                $areas = new Collection($areas);
                $areas->push([
                    'name' => '- LOKASI KOTA/KAB BELUM TERDATA',
                    'code_bps' => '',
                ]);
                $counts = $this->getDashboardListData('domicile_kabkota_bps_id', [], null);
                $counts_baru = $this->getDashboardListData('domicile_kabkota_bps_id', [['<>', 'created_by', 2]], null);
                $areas->transform(function ($area) use (&$counts, &$counts_baru) {
                    $area['data'] = isset($counts[$area['code_bps']]) ? $counts[$area['code_bps']] : (object) [];
                    $area['data_baru'] = isset($counts_baru[$area['code_bps']]) ? $counts_baru[$area['code_bps']] : (object) [];
                    return $area;
                });
                break;
            case 'kabkota':
                $areas = $getChildAreas($code_bps);
                $areas = new Collection($areas);
                $areas->push([
                    'name' => '- LOKASI KEC BELUM TERDATA',
                    'code_bps' => '',
                ]);
                $counts = $this->getDashboardListData('domicile_kec_bps_id', [['=', 'domicile_kabkota_bps_id', $code_bps]], null);
                $counts_baru = $this->getDashboardListData(
                    'domicile_kec_bps_id',
                    [
                        ['=', 'domicile_kabkota_bps_id', $code_bps],
                        ['<>', 'created_by', 2],
                    ],
                    null
                );
                $areas->transform(function ($area) use (&$counts, &$counts_baru) {
                    $area['data'] = isset($counts[$area['code_bps']]) ? $counts[$area['code_bps']] : (object) [];
                    $area['data_baru'] = isset($counts_baru[$area['code_bps']]) ? $counts_baru[$area['code_bps']] : (object) [];
                    return $area;
                });
                break;
            case 'kec':
                $areas = $getChildAreas($code_bps);
                $areas = new Collection($areas);
                $areas->push([
                    'name' => '- LOKASI KEL BELUM TERDATA',
                    'code_bps' => '',
                ]);
                $counts = $this->getDashboardListData(
                    'domicile_kel_bps_id',
                    [
                        ['=', 'domicile_kabkota_bps_id', substr($code_bps, 0, 4)],
                        ['=', 'domicile_kec_bps_id', $code_bps],
                    ],
                    null
                );
                $counts_baru = $this->getDashboardListData(
                    'domicile_kel_bps_id',
                    [
                        ['=', 'domicile_kabkota_bps_id', substr($code_bps, 0, 4)],
                        ['=', 'domicile_kec_bps_id', $code_bps],
                        ['<>', 'created_by', 2],
                    ],
                    null
                );
                $areas->transform(function ($area) use (&$counts, &$counts_baru) {
                    $area['data'] = isset($counts[$area['code_bps']]) ? $counts[$area['code_bps']] : (object) [];
                    $area['data_baru'] = isset($counts_baru[$area['code_bps']]) ? $counts_baru[$area['code_bps']] : (object) [];
                    return $area;
                });
                break;
            case 'kel':
                $areas = new Collection([]);
                $counts = $this->getDashboardListData(
                    'domicile_rw',
                    [
                        ['=', 'domicile_kabkota_bps_id', substr($code_bps, 0, 4)],
                        ['=', 'domicile_kec_bps_id', substr($code_bps, 0, 7)],
                        ['=', 'domicile_kel_bps_id', $code_bps],
                    ],
                    'cast(domicile_rw as unsigned) asc'
                );
                $counts_baru = $this->getDashboardListData(
                    'domicile_rw',
                    [
                        ['=', 'domicile_kabkota_bps_id', substr($code_bps, 0, 4)],
                        ['=', 'domicile_kec_bps_id', substr($code_bps, 0, 7)],
                        ['=', 'domicile_kel_bps_id', $code_bps],
                        ['<>', 'created_by', 2],
                    ],
                    'cast(domicile_rw as unsigned) asc'
                );
                foreach ($counts as $rw => $count) {
                    if ($rw !== null && $rw !== '') {
                        $areas->push([
                            'name' => 'RW ' . $rw,
                            'code_bps' => $code_bps,
                            'rw' => $rw,
                        ]);
                    }
                }
                $areas->push([
                    'name' => '- LOKASI RW BELUM TERDATA',
                    'code_bps' => '',
                    'rw' => '',
                ]);
                $areas->transform(function ($area) use (&$counts, &$counts_baru) {
                    $area['data'] = isset($counts[$area['rw']]) ? $counts[$area['rw']] : (object) [];
                    $area['data_baru'] = isset($counts_baru[$area['rw']]) ? $counts_baru[$area['rw']] : (object) [];
                    return $area;
                });
                break;
            case 'rw':
                $areas = new Collection([]);
                $counts = $this->getDashboardListData(
                    'domicile_rt',
                    [
                        ['=', 'domicile_kabkota_bps_id', substr($code_bps, 0, 4)],
                        ['=', 'domicile_kec_bps_id', substr($code_bps, 0, 7)],
                        ['=', 'domicile_kel_bps_id', $code_bps],
                        ['=', 'domicile_rw', $rw],
                    ],
                    'cast(domicile_rt as unsigned) asc'
                );
                $counts_baru = $this->getDashboardListData(
                    'domicile_rt',
                    [
                        ['=', 'domicile_kabkota_bps_id', substr($code_bps, 0, 4)],
                        ['=', 'domicile_kec_bps_id', substr($code_bps, 0, 7)],
                        ['=', 'domicile_kel_bps_id', $code_bps],
                        ['=', 'domicile_rw', $rw],
                        ['<>', 'created_by', 2],
                    ],
                    'cast(domicile_rt as unsigned) asc'
                );
                foreach ($counts as $rt => $count) {
                    if ($rt !== null && $rt !== '') {
                        $areas->push([
                            'name' => 'RT ' . $rt,
                            'code_bps' => $code_bps,
                            'rw' => $rw,
                            'rt' => $rt,
                        ]);
                    }
                }
                $areas->push([
                    'name' => '- LOKASI RT BELUM TERDATA',
                    'code_bps' => '',
                    'rw' => '',
                    'rt' => '',
                ]);
                $areas->transform(function ($area) use (&$counts, &$counts_baru) {
                    $area['data'] = isset($counts[$area['rt']]) ? $counts[$area['rt']] : (object) [];
                    $area['data_baru'] = isset($counts_baru[$area['rt']]) ? $counts_baru[$area['rt']] : (object) [];
                    return $area;
                });
                break;
        }

        return $areas;
    }

    public function getAreaByUser()
    {
        $user = Yii::$app->user;
        $authUserModel = $user->identity;
        $params = [];

        if ($user->can('staffKabkota')) {
            $area = Area::find()->where(['id' => $authUserModel->kabkota_id])->one();
            $params['domicile_kabkota_bps_id'] = $area->code_bps;
        } elseif ($user->can('staffKec')) {
            $area = Area::find()->where(['id' => $authUserModel->kabkota_id])->one();
            $params['domicile_kabkota_bps_id'] = $area->code_bps;
            $area = Area::find()->where(['id' => $authUserModel->kec_id])->one();
            $params['domicile_kec_bps_id'] = $area->code_bps;
        } elseif ($user->can('staffKel') || $user->can('staffRW') || $user->can('trainer')) {
            $area = Area::find()->where(['id' => $authUserModel->kabkota_id])->one();
            $params['domicile_kabkota_bps_id'] = $area->code_bps;
            $area = Area::find()->where(['id' => $authUserModel->kec_id])->one();
            $params['domicile_kec_bps_id'] = $area->code_bps;
            $area = Area::find()->where(['id' => $authUserModel->kel_id])->one();
            $params['domicile_kel_bps_id'] = $area->code_bps;
            $params['domicile_rw'] = $authUserModel->rw;
        }

        return $params;
    }

    /* APPROVAL */

    /**
     * Generates common params for approval-related actions (approval dashboard, list, single/bulk approve)
     * @return array
     */
    public function getApprovalParams()
    {
        $authUser = Yii::$app->user;
        $authUserModel = $authUser->identity;
        $params = [
            'type' => null,
            'area_id' => null,
        ];

        switch ($authUserModel->role) {
            case User::ROLE_STAFF_KEL:
                $params['type'] = Beneficiary::TYPE_KEL;
                $params['area_id'] = $authUserModel->kel_id;
                break;
            case User::ROLE_STAFF_KEC:
                $params['type'] = Beneficiary::TYPE_KEC;
                $params['area_id'] = $authUserModel->kec_id;
                break;
            case User::ROLE_STAFF_KABKOTA:
                $params['type'] = Beneficiary::TYPE_KABKOTA;
                $params['area_id'] = $authUserModel->kabkota_id;
                break;
            case User::ROLE_STAFF_OPD:
            case User::ROLE_STAFF_PROV:
            case User::ROLE_PIMPINAN:
            case User::ROLE_ADMIN:
                $params['type'] = Beneficiary::TYPE_PROVINSI;
                break;
            default:
                throw new ForbiddenHttpException(Yii::t('app', 'error.role.permission'));
                break;
        }

        return $params;
    }

    public function actionApprovalDashboard()
    {
        $params = $this->getApprovalParams();
        $model = new BeneficiaryApproval();
        $model->tahap = Arr::get(Yii::$app->request->getQueryParams(), 'tahap');
        return $model->getDashboardApproval($params);
    }

    public function actionApprovalList()
    {
        $approvalParams = $this->getApprovalParams();
        $params = Yii::$app->request->getQueryParams();
        $params['type'] = Arr::get($approvalParams, 'type');

        $user = Yii::$app->user;
        $authUserModel = $user->identity;

        $search = new BeneficiarySearch();
        $search->userRole = $authUserModel->role;
        $search->tahap = Arr::get($params, 'tahap');
        $search->scenario = BeneficiarySearch::SCENARIO_LIST_APPROVAL;

        $params = array_merge($params, $this->getAreaByUser());

        $result = $search->validate();
        if ($result === false) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(422);
            return $search->getErrors();
        }

        return $search->search($params);
    }

    public function actionApproval($id)
    {
        $model = $this->findModel($id, $this->modelClass);
        if ($model->status_verification < Beneficiary::STATUS_VERIFIED) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(400);

            return 'Bad Request: Invalid Object Status';
        }

        $params = $this->getApprovalParams();

        return $this->processSingleApproval($model, $params);
    }

    public function actionBulkApproval()
    {
        $params = $this->getApprovalParams();

        return $this->processBulkApproval($params);
    }

    public function actionCurrentTahap()
    {
        return BeneficiaryHelper::getCurrentTahap();
    }

    /**
     * Get status_verification value based on type and action
     * @param string $type Area type (provinsi | kabkota | kec | kel | rw)
     * @param string $action Approval action (APPROVE | REJECT)
     * @return integer
     * @throws \yii\web\BadRequestHttpException
     * @throws \yii\web\ForbiddenHttpException
     */
    public function getNewStatusVerification($type, $action)
    {
        if (!array_key_exists($type, BeneficiaryApproval::APPROVAL_MAP)) {
            throw new ForbiddenHttpException(Yii::t('app', 'error.role.permission'));
        };

        if ($action !== Beneficiary::ACTION_APPROVE &&
            $action !== Beneficiary::ACTION_REJECT
        ) {
            throw new BadRequestHttpException('Bad Request: Invalid Action');
        }

        if ($action === Beneficiary::ACTION_APPROVE) {
            return BeneficiaryApproval::APPROVAL_MAP[$type]['approved'];
        } elseif ($action === Beneficiary::ACTION_REJECT) {
            return BeneficiaryApproval::APPROVAL_MAP[$type]['rejected'];
        }
    }

    protected function processSingleApproval($model, $params)
    {
        $newStatusVerification = $this->getNewStatusVerification(
            Arr::get($params, 'type'),
            Yii::$app->request->post('action')
        );

        $model->status_verification = $newStatusVerification;
        if ($model->save(false) === false) {
            throw new ServerErrorHttpException('Failed to process the object for unknown reason.');
        }

        $response = Yii::$app->getResponse();
        $response->setStatusCode(200);

        return 'ok';
    }

    protected function processBulkApproval($params)
    {
        $ids = Yii::$app->request->post('ids');

        $newStatusVerification = $this->getNewStatusVerification(
            Arr::get($params, 'type'),
            Yii::$app->request->post('action')
        );

        if ($newStatusVerification && $ids) {
            $currentTahap = BeneficiaryHelper::getCurrentTahap();
            $statusVerificationColumn = BeneficiaryHelper::getStatusVerificationColumn($currentTahap['current_tahap_verval']);

            // bulk action
            Beneficiary::updateAll(
                [
                    'status_verification' => $newStatusVerification,
                    "{$statusVerificationColumn}" => $newStatusVerification,
                    'updated_by' => ModelHelper::getLoggedInUserId(),
                    'updated_at' => time(),
                ],
                [
                    'and',
                    ['=', 'status', Beneficiary::STATUS_ACTIVE],
                    ['in', 'id', $ids],
                ]
            );
        }

        $response = Yii::$app->getResponse();
        $response->setStatusCode(200);

        return 'ok';
    }
}
