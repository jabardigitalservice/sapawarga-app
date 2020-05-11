<?php

namespace app\modules\v1\controllers;

use app\models\Area;
use app\models\Beneficiary;
use app\models\BeneficiarySearch;
use app\validator\NikRateLimitValidator;
use app\validator\NikValidator;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Yii;
use yii\base\DynamicModel;
use yii\filters\AccessControl;
use yii\web\HttpException;
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
        // setup access
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => ['index', 'view', 'create', 'update', 'delete', 'nik', 'check-exist-nik', 'dashboard-list', 'dashboard-summary', 'approval'],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['index', 'view', 'create', 'update', 'delete', 'nik', 'check-exist-nik', 'dashboard-list', 'dashboard-summary'],
                    'roles' => ['admin', 'staffProv', 'staffKabkota', 'staffKec', 'staffKel', 'staffRW', 'trainer'],
                ],
                [
                    'allow' => true,
                    'actions' => ['approval'],
                    'roles' => ['admin', 'staffKel'],
                ]
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

        $user = Yii::$app->user;
        $authUserModel = $user->identity;

        $search = new BeneficiarySearch();
        $search->userRole = $authUserModel->role;
        $search->scenario = BeneficiarySearch::SCENARIO_LIST_USER;

        if ($user->can('staffKabkota')) {
            $area = Area::find()->where(['id' => $authUserModel->kabkota_id])->one();
            $params['domicile_kabkota_bps_id'] = $area->code_bps;
        } elseif ($user->can('staffKec')) {
            $area = Area::find()->where(['id' => $authUserModel->kec_id])->one();
            $params['domicile_kec_bps_id'] = $area->code_bps;
        } elseif ($user->can('staffKel') || $user->can('staffRW') || $user->can('trainer')) {
            $area = Area::find()->where(['id' => $authUserModel->kel_id])->one();
            $params['domicile_kel_bps_id'] = $area->code_bps;
            $params['domicile_rw'] = $authUserModel->rw;
        }

        return $search->search($params);
    }

    /**
     * @param $id
     * @return mixed|\app\models\Beneficiery
     */
    public function actionCheckExistNik($id)
    {
        $model = Beneficiary::find()
            ->where(['nik' => $id])
            ->andWhere(['!=', 'status', Beneficiary::STATUS_DELETED])
            ->exists();

        $response = Yii::$app->getResponse();
        $response->setStatusCode(200);

        return $model;
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
            'timeout'  => 30.00,
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
            throw new HttpException(408, 'Request Time-out');
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

        return $log;
    }

    public function actionDashboardSummary()
    {
        $params = Yii::$app->request->getQueryParams();

        $type = Arr::get($params, 'type');
        $code_bps = Arr::get($params, 'code_bps');
        $rw = Arr::get($params, 'rw');
        $transformCount = function ($lists) {
            $status_maps = [
                '1' => 'pending',
                '2' => 'rejected',
                '3' => 'approved',
                '4' => 'rejected_kec',
                '5' => 'approved_kec',
                '6' => 'rejected_kabkota',
                '7' => 'approved_kabkota',
            ];
            $data = [];
            $jml = Arr::pluck($lists, 'jumlah', 'status_verification');
            $total = 0;
            foreach ($status_maps as $key => $map) {
                $data[$map] = isset($jml[$key]) ? intval($jml[$key]) : 0;
                $total += $data[$map];
            }
            $data['total'] = $total;
            return $data;
        };
        switch ($type) {
            case 'provinsi':
                $counts = (new \yii\db\Query())
                    ->select(['status_verification','COUNT(*) AS jumlah'])
                    ->from('beneficiaries')
                    ->groupBy(['status_verification'])
                    ->createCommand()
                    ->queryAll();
                $counts = new Collection($counts);
                $counts = $transformCount($counts);
                break;
            case 'kabkota':
                $counts = (new \yii\db\Query())
                    ->select(['status_verification','COUNT(*) AS jumlah'])
                    ->from('beneficiaries')
                    ->where(['=','kabkota_bps_id', $code_bps])
                    ->groupBy(['status_verification'])
                    ->createCommand()
                    ->queryAll();
                $counts = new Collection($counts);
                $counts = $transformCount($counts);
                break;
            case 'kec':
                $counts = (new \yii\db\Query())
                    ->select(['status_verification','COUNT(*) AS jumlah'])
                    ->from('beneficiaries')
                    ->where(['=','kec_bps_id', $code_bps])
                    ->groupBy(['status_verification'])
                    ->createCommand()
                    ->queryAll();
                $counts = new Collection($counts);
                $counts = $transformCount($counts);
                break;
            case 'kel':
                $counts = (new \yii\db\Query())
                    ->select(['status_verification','COUNT(*) AS jumlah'])
                    ->from('beneficiaries')
                    ->where(['=','kel_bps_id', $code_bps])
                    ->groupBy(['status_verification'])
                    ->createCommand()
                    ->queryAll();
                $counts = new Collection($counts);
                $counts = $transformCount($counts);
                break;
            case 'rw':
                $counts = (new \yii\db\Query())
                    ->select(['status_verification','COUNT(*) AS jumlah'])
                    ->from('beneficiaries')
                    ->where(['=','kel_bps_id', $code_bps])
                    ->andWhere(['=','rw', $rw])
                    ->groupBy(['status_verification'])
                    ->createCommand()
                    ->queryAll();
                $counts = new Collection($counts);
                $counts = $transformCount($counts);
                break;
        }
        return $counts;
    }

    public function actionDashboardList()
    {
        $params = Yii::$app->request->getQueryParams();

        $type = Arr::get($params, 'type');
        $code_bps = Arr::get($params, 'code_bps');
        $rw = Arr::get($params, 'rw');

        $transformCount = function ($lists) {
            $status_maps = [
                '1' => 'pending',
                '2' => 'rejected',
                '3' => 'approved',
                '4' => 'rejected_kec',
                '5' => 'approved_kec',
                '6' => 'rejected_kabkota',
                '7' => 'approved_kabkota',
            ];
            $data = [];
            $jml = $lists->pluck('jumlah', 'status_verification');
            $total = 0;
            foreach ($status_maps as $key => $map) {
                $data[$map] = isset($jml[$key]) ? intval($jml[$key]) : 0;
                $total += $data[$map];
            }
            $data['total'] = $total;
            return $data;
        };

        switch ($type) {
            case 'provinsi':
                $areas = (new \yii\db\Query())
                    ->select(['code_bps', 'name'])
                    ->from('areas')
                    ->where(['=','code_bps_parent', '32'])
                    ->createCommand()
                    ->queryAll();
                $areas = new Collection($areas);
                $areas->push([
                    'name' => '- LOKASI KOTA/KAB BELUM TERDATA',
                    'code_bps' => '',
                ]);
                $counts = (new \yii\db\Query())
                    ->select(['kabkota_bps_id', 'status_verification','COUNT(*) AS jumlah'])
                    ->from('beneficiaries')
                    ->groupBy(['kabkota_bps_id', 'status_verification'])
                    ->createCommand()
                    ->queryAll();
                $counts = new Collection($counts);
                $counts = $counts->groupBy('kabkota_bps_id');
                $counts->transform($transformCount);
                $areas->transform(function ($area) use (&$counts) {
                    $area['data'] = isset($counts[$area['code_bps']]) ? $counts[$area['code_bps']] : (object) [];
                    return $area;
                });
                break;
            case 'kabkota':
                $areas = (new \yii\db\Query())
                    ->select(['code_bps', 'name'])
                    ->from('areas')
                    ->where(['=','code_bps_parent', $code_bps])
                    ->createCommand()
                    ->queryAll();
                $areas = new Collection($areas);
                $areas->push([
                    'name' => '- LOKASI KEC BELUM TERDATA',
                    'code_bps' => '',
                ]);
                $counts = (new \yii\db\Query())
                    ->select(['kec_bps_id', 'status_verification','COUNT(*) AS jumlah'])
                    ->from('beneficiaries')
                    ->where(['=','kabkota_bps_id', $code_bps])
                    ->groupBy(['kec_bps_id', 'status_verification'])
                    ->createCommand()
                    ->queryAll();
                $counts = new Collection($counts);
                $counts = $counts->groupBy('kec_bps_id');
                $counts->transform($transformCount);
                $areas->transform(function ($area) use (&$counts) {
                    $area['data'] = isset($counts[$area['code_bps']]) ? $counts[$area['code_bps']] : (object) [];
                    return $area;
                });
                break;
            case 'kec':
                $areas = (new \yii\db\Query())
                    ->select(['code_bps', 'name'])
                    ->from('areas')
                    ->where(['=','code_bps_parent', $code_bps])
                    ->createCommand()
                    ->queryAll();
                $areas = new Collection($areas);
                $areas->push([
                    'name' => '- LOKASI KEL BELUM TERDATA',
                    'code_bps' => '',
                ]);
                $counts = (new \yii\db\Query())
                    ->select(['kel_bps_id', 'status_verification','COUNT(*) AS jumlah'])
                    ->from('beneficiaries')
                    ->where(['=','kec_bps_id', $code_bps])
                    ->groupBy(['kel_bps_id', 'status_verification'])
                    ->createCommand()
                    ->queryAll();
                $counts = new Collection($counts);
                $counts = $counts->groupBy('kel_bps_id');
                $counts->transform($transformCount);
                $areas->transform(function ($area) use (&$counts) {
                    $area['data'] = isset($counts[$area['code_bps']]) ? $counts[$area['code_bps']] : (object) [];
                    return $area;
                });
                break;
            case 'kel':
                $areas = new Collection([]);
                $counts = (new \yii\db\Query())
                    ->select(['rw', 'status_verification','COUNT(*) AS jumlah'])
                    ->from('beneficiaries')
                    ->where(['=','kel_bps_id', $code_bps])
                    ->groupBy(['rw', 'status_verification'])
                    ->orderBy('cast(rw as unsigned) asc')
                    ->createCommand()
                    ->queryAll();
                $counts = new Collection($counts);
                $counts = $counts->groupBy('rw');
                $counts->transform($transformCount);
                foreach ($counts as $rw => $count) {
                    $areas->push([
                        'name' => 'RW ' . $rw,
                        'code_bps' => $code_bps,
                        'rw' => $rw,
                    ]);
                }
                $areas->push([
                    'name' => '- LOKASI RW BELUM TERDATA',
                    'code_bps' => '',
                    'rw' => '',
                ]);
                $areas->transform(function ($area) use (&$counts) {
                    $area['data'] = isset($counts[$area['rw']]) ? $counts[$area['rw']] : (object) [];
                    return $area;
                });
                break;
            case 'rw':
                $areas = new Collection([]);
                $counts = (new \yii\db\Query())
                    ->select(['rt', 'status_verification','COUNT(*) AS jumlah'])
                    ->from('beneficiaries')
                    ->where(['=','kel_bps_id', $code_bps])
                    ->andWhere(['=','rw', $rw])
                    ->groupBy(['rt', 'status_verification'])
                    ->orderBy('cast(rt as unsigned) asc')
                    ->createCommand()
                    ->queryAll();
                $counts = new Collection($counts);
                $counts = $counts->groupBy('rt');
                $counts->transform($transformCount);
                foreach ($counts as $rt => $count) {
                    $areas->push([
                        'name' => 'RT ' . $rt,
                        'code_bps' => $code_bps,
                        'rw' => $rw,
                        'rt' => $rt,
                    ]);
                }
                $areas->push([
                    'name' => '- LOKASI RT BELUM TERDATA',
                    'code_bps' => '',
                    'rw' => '',
                    'rt' => '',
                ]);
                $areas->transform(function ($area) use (&$counts) {
                    $area['data'] = isset($counts[$area['rt']]) ? $counts[$area['rt']] : (object) [];
                    return $area;
                });
                break;
        }

        return $areas;
    }

    public function actionApproval()
    {
        $model = $this->findModel(Yii::$app->request->post('id'), $this->modelClass);

        if ($model->status_verification < Beneficiary::STATUS_APPROVED) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(400);

            return 'Bad Request: Invalid Object Status';
        }

        return $this->processApproval($model);
    }

    protected function processApproval($model)
    {
        $action = Yii::$app->request->post('action');

        $currentUserId = Yii::$app->user->getId();

        if ($action === Beneficiary::ACTION_APPROVE) {
            $model->status_verification = Beneficiary::STATUS_APPROVED_KEL;
        } elseif ($action === Beneficiary::ACTION_REJECT) {
            $model->status_verification = Beneficiary::STATUS_REJECTED_KEL;
        } else {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(400);
            return 'Bad Request: Invalid Action';
        }

        if ($model->save(false) === false) {
            throw new ServerErrorHttpException('Failed to process the object for unknown reason.');
        }

        $response = Yii::$app->getResponse();
        $response->setStatusCode(200);

        return 'ok';
    }
}
