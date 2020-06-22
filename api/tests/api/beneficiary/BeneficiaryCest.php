<?php

use app\models\Beneficiary;

class BeneficiaryCest
{
    private $endpointBeneficiaries = '/v1/beneficiaries';
    private $kabkotaBandung = '3273';
    private $kecBandung = '3273230';
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

    protected function loadDataByTahap(ApiTester $I)
    {
        $I->haveInDatabase('beneficiaries', [
            'id' => 1,
            'nik' => '3200000000000001',
            'domicile_kabkota_bps_id' => $this->kabkotaBandung,
            'domicile_kec_bps_id' => $this->kecBandung,
            'domicile_kel_bps_id' => $this->kelBandung,
            'domicile_rw' => '1',
            'domicile_rt' => '1',
            'domicile_address' => 'Address',
            'status_verification' => Beneficiary::STATUS_VERIFIED,
            'status' => Beneficiary::STATUS_ACTIVE,
            'name' => 'Name',
            'created_at' => 0,
            'updated_at' => 0,
            'tahap_1_verval' => Beneficiary::STATUS_VERIFIED,
            'tahap_2_verval' => Beneficiary::STATUS_VERIFIED,
        ]);

        $I->haveInDatabase('beneficiaries', [
            'id' => 2,
            'nik' => '3200000000000002',
            'domicile_kel_bps_id' => $this->kelBandung,
            'domicile_rw' => '1',
            'status_verification' => Beneficiary::STATUS_VERIFIED,
            'status' => Beneficiary::STATUS_ACTIVE,
            'name' => 'Name',
            'created_at' => 0,
            'updated_at' => 0,
            'tahap_1_verval' => null,
            'tahap_2_verval' => Beneficiary::STATUS_VERIFIED,
        ]);

        $I->haveInDatabase('beneficiaries', [
            'id' => 3,
            'nik' => '3200000000000003',
            'domicile_kel_bps_id' => $this->kelBandung,
            'domicile_rw' => '1',
            'status_verification' => Beneficiary::STATUS_VERIFIED,
            'status' => Beneficiary::STATUS_ACTIVE,
            'name' => 'Name',
            'created_at' => 0,
            'updated_at' => 0,
            'tahap_1_verval' => null,
            'tahap_2_verval' => null,
            'tahap_3_verval' => Beneficiary::STATUS_VERIFIED,
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

    /**
     * @before loadDataByTahap
     */
    public function getStaffKelListFilterByTahap(ApiTester $I)
    {
        $I->amStaff('staffrw');

        $I->sendGET($this->endpointBeneficiaries . '?tahap=1');
        $I->canSeeResponseCodeIs(200);
        $I->seeHttpHeader('X-Pagination-Total-Count', 1);

        $data = $I->grabDataFromResponseByJsonPath('$.data.items');
        $I->assertEquals(1, $data[0][0]['id']);

        $I->sendGET($this->endpointBeneficiaries . '?tahap=2');
        $I->canSeeResponseCodeIs(200);
        $I->seeHttpHeader('X-Pagination-Total-Count', 2);

        $data = $I->grabDataFromResponseByJsonPath('$.data.items');
        $I->assertEquals(1, $data[0][0]['id']);
        $I->assertEquals(2, $data[0][1]['id']);
    }

    /**
     * @before loadDataByTahap
     */
    public function putStaffKelEdit(ApiTester $I)
    {
        $I->haveInDatabase('beneficiaries_current_tahap', [
            'id' => 1,
            'current_tahap_verval' => 3,
            'current_tahap_bnba' => 2,
        ]);

        $I->amStaff('staffkel');

        $data = [
            'status_verification' => Beneficiary::STATUS_APPROVED_KEL,
        ];

        $I->sendPUT("{$this->endpointBeneficiaries}/1", $data);
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeInDatabase('beneficiaries', [
            'id' => 1,
            'status_verification' => Beneficiary::STATUS_APPROVED_KEL,
            'tahap_3_verval' => Beneficiary::STATUS_APPROVED_KEL,
        ]);
    }
}
