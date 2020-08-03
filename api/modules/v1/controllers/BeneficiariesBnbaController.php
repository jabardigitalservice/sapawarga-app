<?php

namespace app\modules\v1\controllers;

use app\models\Area;
use app\models\BansosBnbaUploadHistory;
use app\models\BansosBnbaDownloadHistory;
use app\models\BeneficiaryBnbaTahapSatu;
use app\models\BeneficiaryBnbaTahapSatuSearch;
use Yii;
use yii\db\Query;
use yii\base\DynamicModel;
use yii\data\ArrayDataProvider;
use yii\data\ActiveDataProvider;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\web\UploadedFile;
use Illuminate\Support\Arr;
use Jdsteam\Sapawarga\Jobs\ExportBnbaWithComplainJob;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;

/**
 * BeneficiariesBnbaTahapSatuController implements the CRUD actions for BeneficiaryBnbaTahapSatu model.
 */
class BeneficiariesBnbaController extends ActiveController
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
            'only' => ['index', 'view', 'download', 'summary'],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['monitoring'],
                    'roles' => ['admin', 'staffProv'],
                ],
                [
                    'allow' => true,
                    'actions' => ['index', 'view', 'download', 'download-status', 'summary', 'upload', 'upload-histories'],
                    'roles' => ['admin', 'staffProv', 'staffKabkota', 'staffKec', 'staffKel', 'staffRW'],
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

    public function actionSummary()
    {
        $params = Yii::$app->request->getQueryParams();
        $params = $this->getAreaByUser($params);

        $kodeKab = Arr::get($params, 'kode_kab');
        $tahap = Arr::get($params, 'tahap');

        // $type is empty means API call from homepage
        if (empty($kodeKab)) {
            $search =  (new \yii\db\Query())
                ->select(['id_tipe_bansos', 'SUM(total) AS total'])
                ->from('beneficiaries_bnba_statistic_type')
                ->where(['tahap_bantuan' => $tahap])
                ->groupBy(['id_tipe_bansos'])
                ->all();
        } else {
            $search = new BeneficiaryBnbaTahapSatuSearch();
            $search = $search->getSummaryByType($params);
        }

        // Reformat result
        $beneficiaryTypes = [
            '1' => Yii::t('app', 'type.beneficiaries.pkh'),
            '2' => Yii::t('app', 'type.beneficiaries.bnpt'),
            '3' => Yii::t('app', 'type.beneficiaries.bnpt_perluasan'),
            '4' => Yii::t('app', 'type.beneficiaries.bansos_tunai'),
            '5' => Yii::t('app', 'type.beneficiaries.bansos_presiden_sembako'),
            '6' => Yii::t('app', 'type.beneficiaries.bansos_provinsi'),
            '7' => Yii::t('app', 'type.beneficiaries.dana_desa'),
            '8' => Yii::t('app', 'type.beneficiaries.bansos_kabkota'),
        ];

        $data = [];

        foreach ($beneficiaryTypes as $key => $val) {
            $data[$val] = 0;
            foreach ($search as $value) {
                if ($key == $value['id_tipe_bansos']) {
                    $data[$val] = intval($value['total']);
                }
            }
        }

        return $data;
    }

    public function actionUpload()
    {
        $user       = Yii::$app->user;
        $filesystem = Yii::$app->fs;
        $kabkotaId  = $user->identity->kabkota_id;
        $file = UploadedFile::getInstanceByName('file');
        $uploadStatus = BansosBnbaUploadHistory::STATUS_SUCCESS;

        // VALIDATIONS
        $model = new DynamicModel(['file' => $file]);

        $model->addRule('file', 'required');
        $model->addRule('file', 'file', ['extensions' => 'xlsx, xls', 'checkExtensionByMimeType' => false]);
        if ($model->validate() === false) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(422);
            return $model->getErrors();
        }

        // validate file's header row
        $reader = ReaderEntityFactory::createXLSXReader();
        $reader->open($file->tempName);

        foreach ($reader->getSheetIterator() as $sheet) {
            // only read data from first sheet
            foreach ($sheet->getRowIterator() as $row) {
                // read header row
                $row_array = $row->toArray();
                if ($row_array != ExportBnbaWithComplainJob::getColumnHeaders()) {
                    $uploadStatus = BansosBnbaUploadHistory::STATUS_TEMPLATE_MISMATCH;
                }
                break;
            }
            break; // no need to read more sheets
        }

        $reader->close();

        // upload and store file
        $kabkota   = Area::findOne(['id' => $kabkotaId]);
        $code      = $kabkota->code_bps;
        $ext          = $file->getExtension();
        $date         = date('Ymd_His');
        $relativePath = "bansos-bnba-noimport/{$code}_{$date}.{$ext}";
        $publicBaseUrl = Yii::$app->params['storagePublicBaseUrl'];

        $filesystem->write($relativePath, file_get_contents($file->tempName));

        $publicUrl = "{$publicBaseUrl}/{$relativePath}";

        $historyData = [
            'user_id' => $user->id,
            'kabkota_name' => $kabkota->name,
            'original_filename' => $file->name,
            'final_url' => $publicUrl,
            'timestamp' => time(),
            'status' => $uploadStatus,
        ];

        $history = new BansosBnbaUploadHistory();
        $history->attributes = $historyData;
        $history->save();

        return $historyData;
    }

    public function actionUploadHistories()
    {
        $user = Yii::$app->user;
        $params = Yii::$app->request->getQueryParams();

        $query = BansosBnbaUploadHistory::find()
          ->where([ 'user_id' => $user->id ]);

        $provider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => Arr::get($params, 'limit', 10),
            ],
            'sort' => [
                'defaultOrder' => [
                    'timestamp' => SORT_DESC,
                ]
            ],
        ]);

        return $provider;
    }

    public function actionDownload()
    {
        $params = Yii::$app->request->getQueryParams();
        $queryParams = [];

        $user = Yii::$app->user;
        $authUserModel = $user->identity;

        $exportType = (isset($params['export_type']) && array_key_exists($params['export_type'], BansosBnbaDownloadHistory::AVAILABLE_TYPES)) ?
          $params['export_type'] :
          BansosBnbaDownloadHistory::TYPE_BNBA_ORIGINAL;

        if (isset($params['tahap_bantuan'])) {
            $queryParams['tahap_bantuan'] = explode(',', $params['tahap_bantuan']);
        } else {
            $data = (new \yii\db\Query())
                ->from('beneficiaries_current_tahap')
                ->all();

            if (count($data)) {
                $queryParams['tahap_bantuan'] = $data[0]['current_tahap_bnba'];
            }
        }
        if ($user->can('staffKabkota')) {
            $parentArea = Area::find()->where(['id' => $authUserModel->kabkota_id])->one();
            $queryParams['kode_kab'] = $parentArea->code_bps;
        } elseif ($user->can('staffProv') || $user->can('admin')) {
            if (isset($params['kode_kab'])) {
                $queryParams['kode_kab'] = explode(',', $params['kode_kab']);
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
        } else {
            return 'Fitur download data BNBA tidak tersedia untuk user ini';
        }

        $jobHistory = new BansosBnbaDownloadHistory;
        $jobHistory->user_id = $user->id;
        $jobHistory->job_type = $exportType;
        $jobHistory->params = $queryParams;
        $jobHistory->created_at = time();
        $jobHistory->total_row = $jobHistory->countAffectedRows();
        $jobHistory->save();

        // export bnba
        $jobHistory->startJob();

        return [
            'history_id' => $jobHistory->id,
        ];
    }

    public function actionDownloadStatus($history_id = null)
    {
        if ($history_id != null) {
            $result = BansosBnbaDownloadHistory::findOne($history_id);
            if (empty($result)) {
                throw new NotFoundHttpException();
            } else {
                return $result;
            }
        } else {
            $user = Yii::$app->user;
            $params = Yii::$app->request->getQueryParams();

            $exportType = (isset($params['export_type']) && array_key_exists($params['export_type'], BansosBnbaDownloadHistory::AVAILABLE_TYPES)) ?
              $params['export_type'] :
              BansosBnbaDownloadHistory::TYPE_BNBA_ORIGINAL;

            $query = BansosBnbaDownloadHistory::find()->where([
                'user_id' => $user->id,
                'export_type' => $exportType,
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

    public function actionMonitoring()
    {
        $user = Yii::$app->user;
        $params = Yii::$app->request->getQueryParams();

        $tahapBantuan = null;
        if (isset($params['tahap_bantuan'])) {
            $tahapBantuan = $params['tahap_bantuan'];
        } else {
            $data = (new \yii\db\Query())
                ->from('beneficiaries_current_tahap')
                ->all();

            if (count($data)) {
                $tahapBantuan = $data[0]['current_tahap_bnba'];
            }
        }

        $rawQuery = <<<SQL
            SELECT
              areas.name,
              kode_kab as code_bps,
              is_dtks_final as type,
              last_updated as last_update
            FROM
              (SELECT
                  kode_kab,
                  MAX(updated_time) as last_updated,
                  CASE is_dtks
                      WHEN 1 THEN 'dtks'
                      ELSE 'non-dtks' # null dan nilai lainnya
                  END is_dtks_final
              FROM beneficiaries_bnba_tahap_1
              WHERE
                (is_deleted <> 1 OR is_deleted IS NULL)
                AND tahap_bantuan = :tahap_bantuan
              GROUP BY is_dtks_final, kode_kab
              ) as monitoring_list
            LEFT JOIN areas ON areas.code_bps = kode_kab
            ;
SQL;
        $query = Yii::$app->db
            ->createCommand($rawQuery, [':tahap_bantuan' => $tahapBantuan]);

        $rows = $query->queryAll();
        $finalRows = array_map(function ($item) {
            $item['last_update'] = strtotime($item['last_update']);
            return $item;
        }, $rows);

        if (isset($params['kode_kab'])) {
            $codeBps = explode(',', $params['kode_kab']);
            $finalRows = array_filter($finalRows, function ($item) use ($codeBps) {
                return in_array($item['code_bps'], $codeBps);
            });
        }
        if (isset($params['bansos_type']) && !empty($params['bansos_type'])) {
            $bansosType = explode(',', $params['bansos_type']);
            $finalRows = array_filter($finalRows, function ($item) use ($bansosType) {
                return in_array($item['type'], $bansosType);
            });
        }

        $pageLimit = Arr::get($params, 'limit', 10);
        $provider = new ArrayDataProvider([
            'allModels' => $finalRows,
            'pagination' => [
                'pageSize' => $pageLimit,
            ],
        ]);

        return $provider;
    }

    public function prepareDataProvider()
    {
        $params = Yii::$app->request->getQueryParams();
        $params = $this->getAreaByUser($params);

        $user = Yii::$app->user;
        $authUserModel = $user->identity;
        $search = new BeneficiaryBnbaTahapSatuSearch();
        $search->userRole = $authUserModel->role;

        return $search->search($params);
    }

    public function getAreaByUser($params)
    {
        $user = Yii::$app->user;
        $authUserModel = $user->identity;

        $search = new BeneficiaryBnbaTahapSatuSearch();
        $search->userRole = $authUserModel->role;

        if ($user->can('staffKabkota')) {
            $area = Area::find()->where(['id' => $authUserModel->kabkota_id])->one();
            $params['kode_kab'] = $area->code_bps;
        } elseif ($user->can('staffKec')) {
            $area = Area::find()->where(['id' => $authUserModel->kabkota_id])->one();
            $params['kode_kab'] = $area->code_bps;
            $area = Area::find()->where(['id' => $authUserModel->kec_id])->one();
            $params['kode_kec'] = $area->code_bps;
        } elseif ($user->can('staffKel') || $user->can('staffRW') || $user->can('trainer')) {
            $area = Area::find()->where(['id' => $authUserModel->kabkota_id])->one();
            $params['kode_kab'] = $area->code_bps;
            $area = Area::find()->where(['id' => $authUserModel->kec_id])->one();
            $params['kode_kec'] = $area->code_bps;
            $area = Area::find()->where(['id' => $authUserModel->kel_id])->one();
            $params['kode_kel'] = $area->code_bps;
            $params['rw'] = $authUserModel->rw;
        }

        return $params;
    }
}
