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
                array_push($conditionals,['=', 'domicile_kabkota_bps_id', $this->codeBps]);
                break;
            case 'kec':
                array_push($conditionals,
                    ['=', 'domicile_kabkota_bps_id', substr($this->codeBps, 0, 4)],
                    ['=', 'domicile_kec_bps_id', $this->codeBps]
                );
                break;
            case 'kel':
                array_push($conditionals,
                    ['=', 'domicile_kabkota_bps_id', substr($this->codeBps, 0, 4)],
                    ['=', 'domicile_kec_bps_id', substr($this->codeBps, 0, 7)],
                    ['=', 'domicile_kel_bps_id', $this->codeBps]
                );
                break;
            case 'rw':
                array_push($conditionals,
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

    /**
     * Returns data for Dashboard Summary
     * @param bool $isNew if true, indicates if data is 'usulan baru', which was created 'by user' instead of 'by system'
     * @return array
     */
    protected function getDashboardSummaryData($isNew)
    {
        $statusVerificationColumn = BeneficiaryHelper::getStatusVerificationColumn($this->tahap);

        $counts = $this->getDashboardSummaryQuery($this->getConditionals($isNew));
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
        switch ($this->type) {
            case 'provinsi':
                $counts = $this->getDashboardSummaryData(false);
                $counts_baru = $this->getDashboardSummaryData(true);
                break;
            case 'kabkota':
                $counts = $this->getDashboardSummaryData(false);
                $counts_baru = $this->getDashboardSummaryData(true);
                break;
            case 'kec':
                $counts = $this->getDashboardSummaryData(false);
                $counts_baru = $this->getDashboardSummaryData(true);
                break;
            case 'kel':
                $counts = $this->getDashboardSummaryData(false);
                $counts_baru = $this->getDashboardSummaryData(true);
                break;
            case 'rw':
                $counts = $this->getDashboardSummaryData(false);
                $counts_baru = $this->getDashboardSummaryData(true);
                break;
        }
        $counts['baru'] = $counts_baru;
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
        $query = $query->createCommand()->queryAll();

        return $query;
    }

    /**
     * Returns data for Dashboard List.
     *
     * @param array $areaColumn name of area column used for grouping
     * @param array $conditionals additional 'where' statements to filter data by BPS code
     * @param array $orderBy only applies to 'kel' and 'rw' types. Sort attribute
     *
     * @return array
     */
    protected function getDashboardListData ($areaColumn, $conditionals, $orderBy) {
        $statusVerificationColumn = BeneficiaryHelper::getStatusVerificationColumn($this->tahap);

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
    public function getDashboardList($params)
    {
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
}
