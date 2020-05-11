<?php

use app\models\Beneficiary;

class BeneficiaryApprovalCest
{
    private $endpointBeneficiaries = '/v1/beneficiaries';
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
            'nik' => '3200000000000003',
            'domicile_kel_bps_id' => $this->kelBandung,
            'domicile_rw' => '1',
            'status_verification' => Beneficiary::STATUS_APPROVED,
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
            'status_verification' => Beneficiary::STATUS_APPROVED,
            'status' => Beneficiary::STATUS_ACTIVE,
            'name' => 'Name',
            'created_at' => 0,
            'updated_at' => 0,
        ]);

        $I->haveInDatabase('beneficiaries', [
            'id' => 3,
            'nik' => '3200000000000001',
            'domicile_kel_bps_id' => $this->kelBandung,
            'status_verification' => Beneficiary::STATUS_APPROVED_KEL,
            'status' => Beneficiary::STATUS_ACTIVE,
            'name' => 'Name',
            'created_at' => 0,
            'updated_at' => 0,
        ]);

        $I->haveInDatabase('beneficiaries', [
            'id' => 4,
            'domicile_kel_bps_id' => $this->kelBekasi,
            'status_verification' => Beneficiary::STATUS_APPROVED,
            'status' => Beneficiary::STATUS_ACTIVE,
            'name' => 'Name',
            'created_at' => 0,
            'updated_at' => 0,
        ]);
    }

    /**
     * @before loadData
     */
    public function getStaffKelListAll(ApiTester $I)
    {
        $I->amStaff('staffkel');

        $I->sendGET($this->endpointBeneficiaries);
        $I->canSeeResponseCodeIs(200);
        $I->seeHttpHeader('X-Pagination-Total-Count', 3);

        $data = $I->grabDataFromResponseByJsonPath('$.data.items');
        $I->assertEquals(3, $data[0][0]['id']);
        $I->assertEquals(2, $data[0][1]['id']);
        $I->assertEquals(1, $data[0][2]['id']);
    }

    /**
     * @before loadData
     */
    public function getStaffKelListFilterByStatus(ApiTester $I)
    {
        $I->amStaff('staffkel');

        $I->sendGET($this->endpointBeneficiaries . '?status_verification=' . Beneficiary::STATUS_APPROVED . '&domicile_rw_like=1');
        $I->canSeeResponseCodeIs(200);
        $I->seeHttpHeader('X-Pagination-Total-Count', 1);

        $data = $I->grabDataFromResponseByJsonPath('$.data.items');
        $I->assertEquals(1, $data[0][0]['id']);
    }

    /**
     * @before loadData
     */
    public function postStaffKelAprrove(ApiTester $I)
    {
        $data = [
            'action' => Beneficiary::ACTION_APPROVE,
            'id' => 1,
        ];

        $I->amStaff('staffkel');

        $I->sendPOST("{$this->endpointBeneficiaries}/approval", $data);
        $I->canSeeResponseCodeIs(200);

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeInDatabase('beneficiaries', [
            'id' => 1,
            'status_verification' => Beneficiary::STATUS_APPROVED_KEL,
        ]);
    }

    /**
     * @before loadData
     */
    public function postStaffKelReject(ApiTester $I)
    {
        $data = [
            'action' => Beneficiary::ACTION_REJECT,
            'id' => 1,
        ];

        $I->amStaff('staffkel');

        $I->sendPOST("{$this->endpointBeneficiaries}/approval", $data);
        $I->canSeeResponseCodeIs(200);

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeInDatabase('beneficiaries', [
            'id' => 1,
            'status_verification' => Beneficiary::STATUS_REJECTED_KEL,
        ]);
    }
}
