<?php

use app\models\Beneficiary;

class BeneficiaryDashboardCest
{
    private $endpointDashboardSummary = '/v1/beneficiaries/dashboard-summary';
    private $endpointDashboardList = '/v1/beneficiaries/dashboard-list';
    private $kabkotaBandung = '3273';
    private $kecBandung = '3273230';
    private $kelBandung = '3273230006';
    private $kabkotaBekasi = '3275';
    private $kecBekasi = '3275012';
    private $kelBekasi = '3275012003';

    protected function loadData(ApiTester $I)
    {
        $I->haveInDatabase('beneficiaries', [
            'id' => 1,
            'nik' => '3200000000000001',
            'domicile_kabkota_bps_id' => $this->kabkotaBandung,
            'domicile_kec_bps_id' => $this->kecBandung,
            'domicile_kel_bps_id' => $this->kelBandung,
            'domicile_rw' => '1',
            'tahap_1_verval' => Beneficiary::STATUS_VERIFIED,
            'status' => Beneficiary::STATUS_ACTIVE,
            'name' => 'Name',
            'created_at' => 0,
            'updated_at' => 0,
        ]);

        $I->haveInDatabase('beneficiaries', [
            'id' => 2,
            'nik' => '3200000000000002',
            'domicile_kabkota_bps_id' => $this->kabkotaBandung,
            'domicile_kec_bps_id' => $this->kecBandung,
            'domicile_kel_bps_id' => $this->kelBandung,
            'domicile_rw' => '2',
            'tahap_1_verval' => Beneficiary::STATUS_REJECT,
            'status' => Beneficiary::STATUS_ACTIVE,
            'name' => 'Name',
            'created_at' => 0,
            'updated_at' => 0,
        ]);

        $I->haveInDatabase('beneficiaries', [
            'id' => 3,
            'nik' => '3200000000000003',
            'domicile_kabkota_bps_id' => $this->kabkotaBandung,
            'domicile_kec_bps_id' => $this->kecBandung,
            'domicile_kel_bps_id' => $this->kelBandung,
            'domicile_rw' => '1',
            'tahap_1_verval' => null,
            'tahap_2_verval' => Beneficiary::STATUS_VERIFIED,
            'status' => Beneficiary::STATUS_ACTIVE,
            'name' => 'Name',
            'created_at' => 0,
            'updated_at' => 0,
        ]);

        $I->haveInDatabase('beneficiaries', [
            'id' => 4,
            'nik' => '3200000000000004',
            'domicile_kabkota_bps_id' => $this->kabkotaBekasi,
            'domicile_kec_bps_id' => $this->kecBekasi,
            'domicile_kel_bps_id' => $this->kelBekasi,
            'domicile_rw' => '1',
            'tahap_1_verval' => Beneficiary::STATUS_VERIFIED,
            'status' => Beneficiary::STATUS_ACTIVE,
            'name' => 'Name',
            'created_at' => 0,
            'updated_at' => 0,
        ]);
    }

    /**
    * @before loadData
    */
    public function getDashboardSummary(ApiTester $I)
    {
        // staffKel
        $I->amStaff('staffkel');

        $I->sendGET("{$this->endpointDashboardSummary}?type=kel&code_bps={$this->kelBandung}&tahap=1");
        $I->canSeeResponseCodeIs(200);

        $data = $I->grabDataFromResponseByJsonPath('$.data');
        $I->assertEquals(1, $data[0]['approved']);
        $I->assertEquals(1, $data[0]['rejected']);
        $I->assertEquals(2, $data[0]['total']);

        // staffProv
        $I->amStaff('staffprov');

        $I->sendGET("{$this->endpointDashboardSummary}?type=provinsi&tahap=1");
        $I->canSeeResponseCodeIs(200);

        $data = $I->grabDataFromResponseByJsonPath('$.data');
        $I->assertEquals(2, $data[0]['approved']);
        $I->assertEquals(1, $data[0]['rejected']);
        $I->assertEquals(3, $data[0]['total']);
    }

    /**
    * @before loadData
    */
    public function getDashboardList(ApiTester $I)
    {
        // staffKel. tahap 1
        $I->amStaff('staffkel');

        $I->sendGET("{$this->endpointDashboardList}?type=kel&code_bps={$this->kelBandung}&tahap=1");
        $I->canSeeResponseCodeIs(200);

        $data = $I->grabDataFromResponseByJsonPath('$.data');
        $I->assertEquals(1, $data[0][0]['rw']);
        $I->assertEquals(1, $data[0][0]['data']['total']);
        $I->assertEquals(2, $data[0][1]['rw']);
        $I->assertEquals(1, $data[0][1]['data']['total']);

        // staffProv, tahap 2
        $I->amStaff('staffprov');

        $I->sendGET("{$this->endpointDashboardList}?type=provinsi&tahap=2");
        $I->canSeeResponseCodeIs(200);

        $data = $I->grabDataFromResponseByJsonPath("$.data[?(@.code_bps == {$this->kabkotaBandung})]");

        $I->assertEquals(1, $data[0]['data']['total']);
    }

}
