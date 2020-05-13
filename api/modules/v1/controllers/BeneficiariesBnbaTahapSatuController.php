<?php

namespace app\modules\v1\controllers;

use app\models\Area;
use app\models\BeneficiaryBnbaTahapSatu;
use app\models\BeneficiaryBnbaTahapSatuSearch;
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
 * BeneficiariesBnbaTahapSatuController implements the CRUD actions for BeneficiaryBnbaTahapSatu model.
 */
class BeneficiariesBnbaTahapSatuController extends ActiveController
{
    public $modelClass = BeneficiaryBnbaTahapSatu::class;

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
            'only' => ['index', 'view', 'create', 'update', 'delete', 'summary'],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['index', 'view', 'create', 'update', 'delete', 'summary'],
                    'roles' => ['admin', 'staffProv', 'staffKabkota', 'staffKec', 'staffKel', 'staffRW', 'trainer'],
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

    public function findModel(string $id, $model)
    {
        $searchedModel = $model::find()
            ->where(['id' => $id])
            ->one();

        if ($searchedModel === null) {
            throw new NotFoundHttpException("Object not found: $id");
        }

        return $searchedModel;
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
        $model = $this->findModel($id, BeneficiaryBnbaTahapSatu::class);
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

        $search = new BeneficiaryBnbaTahapSatuSearch();
        $search->userRole = $authUserModel->role;
        $search->scenario = BeneficiaryBnbaTahapSatuSearch::SCENARIO_LIST_USER;

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

        return $search->search($params);
    }

    public function actionSummary()
    {
        set_time_limit(0);
        $params = Yii::$app->request->getQueryParams();

        $kode_kab = Arr::get($params, 'kode_kab');
        $kode_kec = Arr::get($params, 'kode_kec');
        $kode_kel = Arr::get($params, 'kode_kel');
        $rw = Arr::get($params, 'rw');
        $rt = Arr::get($params, 'rt');
        if (!empty($rt)) {
            $type = 'rt';
        } elseif (!empty($rw)) {
            $type = 'rw';
        } elseif (!empty($kode_kel)) {
            $type = 'kel';
        } elseif (!empty($kode_kec)) {
            $type = 'kec';
        } elseif (!empty($kode_kab)) {
            $type = 'kabkota';
        } else {
            $type = 'provinsi';
        }
        $transformCount = function ($lists) {
            $status_maps = [
                '1' => 'pkh',
                '2' => 'bpnt',
                '3' => 'bpnt_perluasan',
                '4' => 'bansos_tunai_kemensos',
                '5' => 'bansos_presiden_sembako_bodebek',
                '6' => 'bansos_provinsi',
                '7' => 'dana_desa',
                '8' => 'bansos_kabkota',
            ];
            $data = [];
            $jml = Arr::pluck($lists, 'jumlah', 'id_tipe_bansos');
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
                    ->select(['id_tipe_bansos','COUNT(*) AS jumlah'])
                    ->from('beneficiaries_bnba_tahap_1')
                    ->groupBy(['id_tipe_bansos'])
                    ->createCommand()
                    ->queryAll();
                $counts = new Collection($counts);
                $counts = $transformCount($counts);
                break;
            case 'kabkota':
                $counts = (new \yii\db\Query())
                    ->select(['id_tipe_bansos','COUNT(*) AS jumlah'])
                    ->from('beneficiaries_bnba_tahap_1')
                    ->where(['=','kode_kab', $kode_kab])
                    ->groupBy(['id_tipe_bansos'])
                    ->createCommand()
                    ->queryAll();
                $counts = new Collection($counts);
                $counts = $transformCount($counts);
                break;
            case 'kec':
                $counts = (new \yii\db\Query())
                    ->select(['id_tipe_bansos','COUNT(*) AS jumlah'])
                    ->from('beneficiaries_bnba_tahap_1')
                    ->where(['=','kode_kec', $kode_kec])
                    ->groupBy(['id_tipe_bansos'])
                    ->createCommand()
                    ->queryAll();
                $counts = new Collection($counts);
                $counts = $transformCount($counts);
                break;
            case 'kel':
                $counts = (new \yii\db\Query())
                    ->select(['id_tipe_bansos','COUNT(*) AS jumlah'])
                    ->from('beneficiaries_bnba_tahap_1')
                    ->where(['=','kode_kel', $kode_kel])
                    ->groupBy(['id_tipe_bansos'])
                    ->createCommand()
                    ->queryAll();
                $counts = new Collection($counts);
                $counts = $transformCount($counts);
                break;
            case 'rw':
                $counts = (new \yii\db\Query())
                    ->select(['id_tipe_bansos','COUNT(*) AS jumlah'])
                    ->from('beneficiaries_bnba_tahap_1')
                    ->where(['=','kode_kel', $kode_kel])
                    ->andWhere(['=','rw', $rw])
                    ->groupBy(['id_tipe_bansos'])
                    ->createCommand()
                    ->queryAll();
                $counts = new Collection($counts);
                $counts = $transformCount($counts);
                break;
            case 'rt':
                $counts = (new \yii\db\Query())
                    ->select(['id_tipe_bansos','COUNT(*) AS jumlah'])
                    ->from('beneficiaries_bnba_tahap_1')
                    ->where(['=','kode_kel', $kode_kel])
                    ->andWhere(['=','rw', $rw])
                    ->andWhere(['=','rt', $rt])
                    ->groupBy(['id_tipe_bansos'])
                    ->createCommand()
                    ->queryAll();
                $counts = new Collection($counts);
                $counts = $transformCount($counts);
                break;
        }
        return $counts;
    }
}
