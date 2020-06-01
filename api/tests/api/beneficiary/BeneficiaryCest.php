<?php

use app\models\Beneficiary;

class BeneficiaryCest
{
    private $endpointBeneficiaries = '/v1/beneficiaries';
    private $kelBandung = '3273230006';
    private $kelBekasi = '3275012003';

    protected function loadData(ApiTester $I)
    {
        $I->haveInDatabase('beneficiaries', [
            'id' => 1,
            'nik' => '3200000000000003',
            'domicile_kel_bps_id' => $this->kelBandung,
            'domicile_rw' => '1',
            'status_verification' => Beneficiary::STATUS_VERIFIED,
            'status' => Beneficiary::STATUS_ACTIVE,
            'name' => 'Name',
            'created_at' => 0,
            'updated_at' => 0,
        ]);

        $I->haveInDatabase('beneficiaries', [
            'id' => 2,
            'nik' => '3200000000000002',
            'domicile_kel_bps_id' => $this->kelBandung,
            'domicile_rw' => '2',
            'status_verification' => Beneficiary::STATUS_VERIFIED,
            'status' => Beneficiary::STATUS_ACTIVE,
            'name' => 'Name',
            'created_at' => 0,
            'updated_at' => 0,
        ]);

        $I->haveInDatabase('beneficiaries', [
            'id' => 3,
            'nik' => '3200000000000001',
            'domicile_kel_bps_id' => $this->kelBandung,
            'domicile_rw' => '1',
            'status_verification' => Beneficiary::STATUS_APPROVED_KEL,
            'status' => Beneficiary::STATUS_ACTIVE,
            'name' => 'Name',
            'created_at' => 0,
            'updated_at' => 0,
        ]);

        $I->haveInDatabase('beneficiaries', [
            'id' => 4,
            'nik' => '3200000000000004',
            'domicile_kel_bps_id' => $this->kelBandung,
            'domicile_rw' => '1',
            'status_verification' => Beneficiary::STATUS_REJECTED_KEL,
            'status' => Beneficiary::STATUS_ACTIVE,
            'name' => 'Name',
            'created_at' => 0,
            'updated_at' => 0,
        ]);

        $I->haveInDatabase('beneficiaries', [
            'id' => 5,
            'domicile_kel_bps_id' => $this->kelBekasi,
            'domicile_rw' => '1',
            'status_verification' => Beneficiary::STATUS_VERIFIED,
            'status' => Beneficiary::STATUS_ACTIVE,
            'name' => 'Name',
            'created_at' => 0,
            'updated_at' => 0,
        ]);
    }


    /**
     * @before loadData
     */
    public function getRWList(ApiTester $I)
    {
        $I->amStaff('staffrw');

        $I->sendGET("{$this->endpointBeneficiaries}");
        $I->canSeeResponseCodeIs(200);
        $I->seeHttpHeader('X-Pagination-Total-Count', 3);

        $data = $I->grabDataFromResponseByJsonPath('$.data.items');
        $I->assertEquals(3, $data[0][0]['id']);
        $I->assertEquals(1, $data[0][1]['id']);
        $I->assertEquals(4, $data[0][2]['id']);
    }

    /**
     * @before loadData
     */
    public function getStaffKelList(ApiTester $I)
    {
        $I->amStaff('staffkel');

        $I->sendGET("{$this->endpointBeneficiaries}");
        $I->canSeeResponseCodeIs(200);
        $I->seeHttpHeader('X-Pagination-Total-Count', 4);

        $data = $I->grabDataFromResponseByJsonPath('$.data.items');
        $I->assertEquals(3, $data[0][0]['id']);
        $I->assertEquals(2, $data[0][1]['id']);
        $I->assertEquals(1, $data[0][2]['id']);
        $I->assertEquals(4, $data[0][3]['id']);
    }

    /**
     * @before loadData
     */
    public function getStaffKelListFilterByStatus(ApiTester $I)
    {
        $I->amStaff('staffrw');

        $I->sendGET($this->endpointBeneficiaries . '?status_verification=' . Beneficiary::STATUS_VERIFIED);
        $I->canSeeResponseCodeIs(200);
        $I->seeHttpHeader('X-Pagination-Total-Count', 3);

        $data = $I->grabDataFromResponseByJsonPath('$.data.items');
        $I->assertEquals(3, $data[0][0]['id']);
        $I->assertEquals(1, $data[0][1]['id']);
        $I->assertEquals(4, $data[0][2]['id']);
    }
}
