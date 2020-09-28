<?php

namespace app\models\beneficiary;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use app\components\BeneficiaryHelper;
use app\models\Area;
use app\models\Beneficiary;
use Yii;

/**
 * BeneficiaryDashboard represents the model behind functionalities related to Bansos Dashboard on webadmin
 *
 */
class BeneficiaryDashboard extends Beneficiary
{
    public const CACHE_KEY_SUMMARY = 'verval-dashboardsummary-';
    public const CACHE_KEY_LIST = 'verval-dashboardlist-';

    public $tahap;
    public $statusVerificationColumn = 'status_verification';

    public $type; // type of dashboard (provinsi | kabkota | kec | kel | rw)
    public $codeBps; // BPS code of the data
    public $rw; // only applies to 'rw' type. RW of the data.

    /**
     * Transforms result from database query into specific data structure, either for Dashboard Sumamary or Dashboard List
     *
     * @param Illuminate\Support\Collection $lists array that needs to be transformed
     * @param string $statusVerificationColumn column to be used as status_verification, depending on 'tahap' parameter value
     *
     * @return array
     */
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

    /**
     * Returns additional 'where' statements to filter data by BPS code
     * @param bool $isNew if true, indicates if data is "usulan baru", which was created "by user" instead of "by system"
     *
     * @return array
     */
    protected function getConditionals($isNew)
    {
        $conditionals = [];
        switch ($this->type) {
            case 'provinsi':
                // no filter by codeBps
                break;
            case 'kabkota':
                array_push($conditionals, ['=', 'domicile_kabkota_bps_id', $this->codeBps]);
                break;
            case 'kec':
                array_push(
                    $conditionals,
                    ['=', 'domicile_kabkota_bps_id', substr($this->codeBps, 0, 4)],
                    ['=', 'domicile_kec_bps_id', $this->codeBps]
                );
                break;
            case 'kel':
                array_push(
                    $conditionals,
                    ['=', 'domicile_kabkota_bps_id', substr($this->codeBps, 0, 4)],
                    ['=', 'domicile_kec_bps_id', substr($this->codeBps, 0, 7)],
                    ['=', 'domicile_kel_bps_id', $this->codeBps]
                );
                break;
            case 'rw':
                array_push(
                    $conditionals,
                    ['=', 'domicile_kabkota_bps_id', substr($this->codeBps, 0, 4)],
                    ['=', 'domicile_kec_bps_id', substr($this->codeBps, 0, 7)],
                    ['=', 'domicile_kel_bps_id', $this->codeBps],
                    ['=', 'domicile_rw', $this->rw]
                );
                break;
        }
        if ($isNew) {
            array_push($conditionals, ['<>', 'created_by', 2]);
        }
        return $conditionals;
    }

    /* VERVAL DASHBOARD - SUMMARY */

    /**
     * Returns database query result for Dashboard Summary
     * @param array $conditionals additional 'where' statements to filter data by BPS code
     * @return array
     */
    protected function getDashboardSummaryQuery($conditionals)
    {
        $statusVerificationColumn = BeneficiaryHelper::getStatusVerificationColumn($this->tahap);

        // base query
        $query = (new \yii\db\Query())
            ->select([$statusVerificationColumn, 'COUNT(*) AS jumlah'])
            ->from('beneficiaries')
            ->where(['=', 'status', Beneficiary::STATUS_ACTIVE]);
        // add conditional filters
        foreach ($conditionals as $conditional) {
            $query = $query->andWhere($conditional);
        }
        $query = $query->groupBy([$statusVerificationColumn])
            ->createCommand()->getRawSql();
            // ->queryAll();

        \yii\helpers\VarDumper::dump($query);
        return $query;
    }

    /**
     * Returns data for Dashboard Summary
     * @param bool $isNew if true, indicates if data is "usulan baru", which was created "by user" instead of "by system"
     * @return array
     */
    protected function getDashboardSummaryData($isNew)
    {
        $statusVerificationColumn = BeneficiaryHelper::getStatusVerificationColumn($this->tahap);

        $cache = Yii::$app->cache;
        $key = self::CACHE_KEY_SUMMARY . $this->tahap . $this->type . $this->codeBps . $this->rw . $isNew;
        // $counts = $cache->get($key);
        // if (!$counts) {
            $counts = $this->getDashboardSummaryQuery($this->getConditionals($isNew));
            // $cache->set($key, $counts, Yii::$app->params['cacheDuration']);
        // }

        $counts = new Collection($counts);
        $counts = $this->transformCount($counts, $statusVerificationColumn);

        return $counts;
    }

    /**
     * Returns data for Dashboard Summary.
     * @return array
     */
    public function getDashboardSummary()
    {
        $counts = $this->getDashboardSummaryData(false);
        $countsNew = $this->getDashboardSummaryData(true);
        $counts['baru'] = $countsNew;
        return $counts;
    }

    /* VERVAL DASHBOARD - LIST */

    /**
     * Returns database query result for Dashboard List.
     *
     * @param array $areaColumn name of area column used for grouping
     * @param array $conditionals additional 'where' statements to filter data by BPS code
     * @param array $orderBy only applies to 'kel' and 'rw' types. Sort attribute
     *
     * @return array
     */
    protected function getDashboardListQuery($areaColumn, $conditionals, $orderBy)
    {
        $statusVerificationColumn = BeneficiaryHelper::getStatusVerificationColumn($this->tahap);

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
        $query = $query->createCommand()->getRawSql();
            // ->queryAll();

        \yii\helpers\VarDumper::dump($query);

        return $query;
    }

    /**
     * Returns data for Dashboard List.
     *
     * @param array $areaColumn name of area column used for grouping
     * @param bool $isNew if true, indicates if data is "usulan baru", which was created "by user" instead of "by system"
     * @param array $orderBy only applies to 'kel' and 'rw' types. Sort attribute
     *
     * @return array
     */
    protected function getDashboardListData($areaColumn, $isNew, $orderBy)
    {
        $statusVerificationColumn = BeneficiaryHelper::getStatusVerificationColumn($this->tahap);

        $transformCount = function ($lists) use ($statusVerificationColumn) {
            return $this->transformCount($lists, $statusVerificationColumn);
        };

        $cache = Yii::$app->cache;
        $key = self::CACHE_KEY_LIST . $this->tahap . $this->type . $this->codeBps . $this->rw . $isNew;
        // $counts = $cache->get($key);
        // if (!$counts) {
            $counts = $this->getDashboardListQuery(
                $areaColumn,
                $this->getConditionals($isNew),
                $orderBy
            );
        //     $cache->set($key, $counts, Yii::$app->params['cacheDuration']);
        // }

        // group by Collection keys
        $counts = new Collection($counts);
        $counts = $counts->groupBy($areaColumn);
        $counts->transform($transformCount);

        return $counts;
    }

    /**
     * Transforms final data from database for Dashboard List
     *
     * @param Illuminate\Support\Collection $area final array that needs to be transformed
     * @param array $counts raw data that will be transformed into $area
     * @param array $countsNew raw data that will be transformed into $area
     *
     * @return array
     */
    protected function transformArea($area, $counts, $countsNew)
    {
        $keyName =  'code_bps';
        switch ($this->type) {
            case 'provinsi':
            case 'kabkota':
            case 'kec':
                $keyName = 'code_bps';
                break;
            case 'kel':
                $keyName = 'rw';
                break;
            case 'rw':
                $keyName = 'rt';
                break;
        }

        $area['data'] = isset($counts[$area[$keyName]]) ? $counts[$area[$keyName]] : (object) [];
        $area['data_baru'] = isset($countsNew[$area[$keyName]]) ? $countsNew[$area[$keyName]] : (object) [];
        return $area;
    }

    /**
     * Returns data for Dashboard List.
     *
     * @param array $params['type'] type of dashboard (provinsi | kabkota | kec | kel | rw)
     * @param array $params['code_bps'] BPS code of the data
     * @param array $params['rw'] RW of the data (optional, applies only to 'rw' type)
     * @param array $params['tahap'] number of tahap (null | 1..4)
     *
     * @return BeneficiaryDashboard
     */
    public function getDashboardList()
    {
        $counts = [];
        $countsNew = [];

        $getChildAreas = function ($parentCodeBps) {
            return (new \yii\db\Query())
                ->select(['code_bps', 'name'])
                ->from('areas')
                ->where(['=', 'code_bps_parent', $parentCodeBps])
                ->createCommand()
                ->queryAll();
        };

        $transformArea = function ($area) use (&$counts, &$countsNew) {
            return $this->transformArea($area, $counts, $countsNew);
        };

        switch ($this->type) {
            case 'provinsi':
                $areas = $getChildAreas('32');
                $areas = new Collection($areas);
                $areas->push([
                    'name' => '- LOKASI KOTA/KAB BELUM TERDATA',
                    'code_bps' => '',
                ]);
                $counts = $this->getDashboardListData('domicile_kabkota_bps_id', false, null);
                $countsNew = $this->getDashboardListData('domicile_kabkota_bps_id', true, null);
                $areas->transform($transformArea);
                break;
            case 'kabkota':
                $areas = $getChildAreas($this->codeBps);
                $areas = new Collection($areas);
                $areas->push([
                    'name' => '- LOKASI KEC BELUM TERDATA',
                    'code_bps' => '',
                ]);
                $counts = $this->getDashboardListData('domicile_kec_bps_id', false, null);
                $countsNew = $this->getDashboardListData('domicile_kec_bps_id', true, null);
                $areas->transform($transformArea);
                break;
            case 'kec':
                $areas = $getChildAreas($this->codeBps);
                $areas = new Collection($areas);
                $areas->push([
                    'name' => '- LOKASI KEL BELUM TERDATA',
                    'code_bps' => '',
                ]);
                $counts = $this->getDashboardListData('domicile_kel_bps_id', false, null);
                $countsNew = $this->getDashboardListData('domicile_kel_bps_id', true, null);
                $areas->transform($transformArea);
                break;
            case 'kel':
                $areas = new Collection([]);
                $counts = $this->getDashboardListData('domicile_rw', false, 'cast(domicile_rw as unsigned) asc');
                $countsNew = $this->getDashboardListData('domicile_rw', true, 'cast(domicile_rw as unsigned) asc');
                foreach ($counts as $rw => $count) {
                    if ($rw !== null && $rw !== '') {
                        $areas->push([
                            'name' => 'RW ' . $rw,
                            'code_bps' => $this->codeBps,
                            'rw' => $rw,
                        ]);
                    }
                }
                $areas->push([
                    'name' => '- LOKASI RW BELUM TERDATA',
                    'code_bps' => '',
                    'rw' => '',
                ]);
                $areas->transform($transformArea);
                break;
            case 'rw':
                $areas = new Collection([]);
                $counts = $this->getDashboardListData('domicile_rt', false, 'cast(domicile_rt as unsigned) asc');
                $countsNew = $this->getDashboardListData('domicile_rt', true, 'cast(domicile_rt as unsigned) asc');
                foreach ($counts as $rt => $count) {
                    if ($rt !== null && $rt !== '') {
                        $areas->push([
                            'name' => 'RT ' . $rt,
                            'code_bps' => $this->codeBps,
                            'rw' => $this->rw,
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
                $areas->transform($transformArea);
                break;
        }

        return $areas;
    }
}
