<?php

namespace app\modules\v1\controllers;

use app\models\Area;
use app\models\Beneficiary;
use app\models\BansosBeneficiariesDownloadHistory;
use Yii;
use yii\db\Query;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use Jdsteam\Sapawarga\Jobs\ExportBeneficiariesJob;
use Illuminate\Support\Arr;

/**
 * BeneficiariesBnbaTahapSatuController implements the CRUD actions for BeneficiaryBnbaTahapSatu model.
 */
class BeneficiariesDownloadController extends ActiveController
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
            'only' => ['download'],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['download'],
                    'roles' => ['admin', 'staffProv', 'staffKabkota', 'staffKec', 'staffKel'],
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

        return $actions;
    }

    public function actionDownload()
    {
        $params = Yii::$app->request->getQueryParams();
        $queryParams = [];
        $finalParams = ['and'];

        $user = Yii::$app->user;
        $authUserModel = $user->identity;

        // special filtering parametes for tahap bantuan
        $colFormat = 'tahap_%d_verval';
        $values = [];
        if (isset($params['tahap_bantuan'])) {
            foreach (explode(',', $params['tahap_bantuan']) as $tahap) {
                $colName = sprintf($colFormat, $tahap);
                $values[$colName] = null;
            }
        } else {
            $data = (new \yii\db\Query())
                ->from('beneficiaries_current_tahap')
                ->all();

            if (count($data)) {
                $colName = sprintf($colFormat, $data[0]['current_tahap_verval']);
                $values[$colName] = null;
            }
        }
        $finalParams[] = ['not', $values];

        // common parameter filtering
        if (isset($params['status_verifikasi'])) {
            $inputValues = explode(',', $params['status_verifikasi']);
            $values = array_map(function ($val) {
                $val = strtoupper($val);
                return constant("app\models\Beneficiary::STATUS_$val");
            }, $inputValues);
            $queryParams['status_verification'] = $values;
        }
        if (isset($params['kode_kel'])) {
            $queryParams['domicile_kel_bps_id'] = explode(',', $params['kode_kel']);
        }
        if (isset($params['kode_kec'])) {
            $queryParams['domicile_kec_bps_id'] = explode(',', $params['kode_kec']);
        }
        if (isset($params['kode_kab'])) {
            $queryParams['domicile_kabkota_bps_id'] = explode(',', $params['kode_kab']);
        }
        if (isset($params['bansos_type'])) {
            $bansosType = explode(',', $params['bansos_type']);
            $isDtks = [];
            if (in_array('dtks', $bansosType)) {
                $isDtks[] = 1;
            }
            if (in_array('non-dtks', $bansosType)) {
                array_push($isDtks, 0, null);
            }
            $queryParams['is_dtks'] = $isDtks;
        }

        // user specific filtering overwriting
        if ($user->can('staffKabkota')) {
            $parentArea = Area::findOne($authUserModel->kabkota_id);
            $queryParams['domicile_kabkota_bps_id'] = $parentArea->code_bps;
        } elseif ($user->can('staffKec')) {
            $parentArea = Area::findOne($authUserModel->kabkota_id);
            $queryParams['domicile_kabkota_bps_id'] = $parentArea->code_bps;
            $parentArea = Area::findOne($authUserModel->kec_id);
            $queryParams['domicile_kec_bps_id'] = $parentArea->code_bps;
        } elseif ($user->can('staffKel')) {
            $parentArea = Area::findOne($authUserModel->kabkota_id);
            $queryParams['domicile_kabkota_bps_id'] = $parentArea->code_bps;
            $parentArea = Area::findOne($authUserModel->kec_id);
            $queryParams['domicile_kec_bps_id'] = $parentArea->code_bps;
            $parentArea = Area::findOne($authUserModel->kel_id);
            $queryParams['domicile_kel_bps_id'] = $parentArea->code_bps;
        }

        // handler utk row dengan kolom kode_kec kosong
        if (isset($queryParams['domicile_kec_bps_id']) && is_array($queryParams['domicile_kec_bps_id'])) {
            $nullValuePos = array_search('0', $queryParams['domicile_kec_bps_id']);
            if ($nullValuePos !== false) {
                // replace 0 with '' and null
                unset($queryParams['domicile_kec_bps_id'][$nullValuePos]);
                array_push($queryParams['domicile_kec_bps_id'], '', null);
            }
        }

        // generate final query parameters
        foreach ($queryParams as $col => $val) {
            $finalParams[] = [$col => $val];
        }

        $jobHistory = new BansosBeneficiariesDownloadHistory;
        $jobHistory->user_id = $user->id;
        $jobHistory->params = $finalParams;
        $jobHistory->row_count = $jobHistory->countAffectedRows();
        $jobHistory->save();

        // export bnba
        $id = Yii::$app->queue->push(new ExportBeneficiariesJob([
            'userId' => $user->id,
            'historyId' => $jobHistory->id,
        ]));

        return [
            'historyId' => $jobHistory->id,
        ];
    }

    public function actionDownloadStatus($history_id = null)
    {
        if ($history_id != null) {
            $result = BansosBeneficiariesDownloadHistory::findOne($history_id);
            if (empty($result)) {
                throw new NotFoundHttpException();
            } else {
                return $result;
            }
        } else {
            $user = Yii::$app->user;
            $params = Yii::$app->request->getQueryParams();

            $query = BansosBeneficiariesDownloadHistory::find()->where([
                'user_id' => $user->id,
            ]);

            $sortOrder = (Arr::get($params, 'order', null) == 'asc') ? SORT_ASC : SORT_DESC;
            return new \yii\data\ActiveDataProvider([
                'query' => $query,
                'pagination' => [
                    'pageSize' => Arr::get($params, 'limit', 10),
                ],
                'sort' => [
                    'defaultOrder' => [
                        'id' => $sortOrder,
                    ]
                ],
            ]);
        }
    }
}
