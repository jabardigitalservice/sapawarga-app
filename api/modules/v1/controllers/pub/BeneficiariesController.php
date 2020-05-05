<?php

namespace app\modules\v1\controllers\pub;

use app\models\pub\Beneficiary;
use app\models\pub\BeneficiarySearch;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
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
            'dashboard-list', 'dashboard-summary',
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

        return $search->getSummaryStatusVerification($params);
    }

    public function prepareDataProvider()
    {
        $params = Yii::$app->request->getQueryParams();

        $search = new BeneficiarySearch();

        return $search->search($params);
    }

    public function actionDashboardSummary()
    {
        $params = Yii::$app->request->getQueryParams();
        
        $type = Arr::get($params, 'type');
        $code_bps = Arr::get($params, 'code_bps');
        $rw = Arr::get($params, 'rw');
        $transformCount = function($lists) {
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
            $jml = Arr::pluck($lists,'jumlah','status_verification');
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

        $transformCount = function($lists) {
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
            $jml = $lists->pluck('jumlah','status_verification');
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
                    'name' => 'LOKASI KOTA/KAB N/A',
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
                $areas->transform(function($area) use (&$counts) {
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
                    'name' => 'LOKASI KEC N/A',
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
                $areas->transform(function($area) use (&$counts) {
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
                    'name' => 'LOKASI KEL N/A',
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
                $areas->transform(function($area) use (&$counts) {
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
                    'name' => 'LOKASI RW N/A',
                    'code_bps' => '',
                    'rw' => '',
                ]);
                $areas->transform(function($area) use (&$counts) {
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
                    'name' => 'LOKASI RT N/A',
                    'code_bps' => '',
                    'rw' => '',
                    'rt' => '',
                ]);
                $areas->transform(function($area) use (&$counts) {
                    $area['data'] = isset($counts[$area['rt']]) ? $counts[$area['rt']] : (object) [];
                    return $area;
                });
                break;
        }

        return $areas;
    }
}
