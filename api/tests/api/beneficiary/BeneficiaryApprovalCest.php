<?php

use app\models\Beneficiary;

class BeneficiaryApprovalCest
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
            'domicile_kabkota_bps_id' => $this->kabkotaBandung,
            'domicile_kec_bps_id' => $this->kecBandung,
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
            'domicile_kabkota_bps_id' => $this->kabkotaBandung,
            'domicile_kec_bps_id' => $this->kecBandung,
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
            'domicile_kabkota_bps_id' => $this->kabkotaBandung,
            'domicile_kec_bps_id' => $this->kecBandung,
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
            'domicile_kabkota_bps_id' => $this->kabkotaBandung,
            'domicile_kec_bps_id' => $this->kecBandung,
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
            'nik' => '3200000000000005',
            'domicile_kabkota_bps_id' => $this->kabkotaBandung,
            'domicile_kec_bps_id' => $this->kecBandung,
            'domicile_kel_bps_id' => $this->kelBandung,
            'domicile_rw' => '1',
            'status_verification' => Beneficiary::STATUS_APPROVED_KEC,
            'status' => Beneficiary::STATUS_ACTIVE,
            'name' => 'Name',
            'created_at' => 0,
            'updated_at' => 0,
        ]);

        $I->haveInDatabase('beneficiaries', [
            'id' => 6,
            'nik' => '3200000000000006',
            'domicile_kabkota_bps_id' => $this->kabkotaBandung,
            'domicile_kec_bps_id' => $this->kecBandung,
            'domicile_kel_bps_id' => $this->kelBandung,
            'domicile_rw' => '1',
            'status_verification' => Beneficiary::STATUS_PENDING,
            'status' => Beneficiary::STATUS_ACTIVE,
            'name' => 'Name',
            'created_at' => 0,
            'updated_at' => 0,
        ]);

        $I->haveInDatabase('beneficiaries', [
            'id' => 7,
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
            'domicile_kel_bps_id' => $this->kelBandung,
            'domicile_rw' => '1',
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
            'status_verification' => Beneficiary::STATUS_APPROVED_KEL,
            'status' => Beneficiary::STATUS_ACTIVE,
            'name' => 'Name',
            'created_at' => 0,
            'updated_at' => 0,
            'tahap_1_verval' => null,
            'tahap_2_verval' => Beneficiary::STATUS_APPROVED_KEL,
        ]);

        $I->haveInDatabase('beneficiaries', [
            'id' => 3,
            'nik' => '3200000000000003',
            'domicile_kel_bps_id' => $this->kelBandung,
            'domicile_rw' => '1',
            'status_verification' => Beneficiary::STATUS_APPROVED_KEL,
            'status' => Beneficiary::STATUS_ACTIVE,
            'name' => 'Name',
            'created_at' => 0,
            'updated_at' => 0,
            'tahap_1_verval' => null,
            'tahap_2_verval' => null,
            'tahap_3_verval' => Beneficiary::STATUS_APPROVED_KEL,
        ]);
    }

    /**
     * @before loadData
     */
    public function getStaffKelList(ApiTester $I)
    {
        $I->amStaff('staffkel');

        $I->sendGET("{$this->endpointBeneficiaries}/approval");
        $I->canSeeResponseCodeIs(200);
        $I->seeHttpHeader('X-Pagination-Total-Count', 5);

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

        // approved status
        $I->sendGET($this->endpointBeneficiaries . '/approval?status_verification=' . Beneficiary::STATUS_APPROVED_KEL . '&domicile_rw_like=1');
        $I->canSeeResponseCodeIs(200);
        $I->seeHttpHeader('X-Pagination-Total-Count', 2);

        $data = $I->grabDataFromResponseByJsonPath('$.data.items');
        $I->assertEquals(3, $data[0][0]['id']);
        $I->assertEquals(5, $data[0][1]['id']);

        // pending/rejected status
        $I->sendGET($this->endpointBeneficiaries . '/approval?status_verification=' . Beneficiary::STATUS_REJECTED_KEL . '&domicile_rw_like=1');
        $I->canSeeResponseCodeIs(200);
        $I->seeHttpHeader('X-Pagination-Total-Count', 1);

        $data = $I->grabDataFromResponseByJsonPath('$.data.items');
        $I->assertEquals(4, $data[0][0]['id']);
    }

     /**
     * @before loadData
     */
    public function getStaffKelDashboard(ApiTester $I)
    {
        $I->amStaff('staffkel');

        $I->sendGET("{$this->endpointBeneficiaries}/approval-dashboard");
        $I->canSeeResponseCodeIs(200);

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
            'data' => [
                'approved' => 2,
                'rejected' => 1,
                'pending' => 2,
                'total' => 5,
            ]
        ]);

        $I->amStaff('staffkec');

        $I->sendGET("{$this->endpointBeneficiaries}/approval-dashboard");
        $I->canSeeResponseCodeIs(200);

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
            'data' => [
                'approved' => 1,
                'rejected' => 0,
                'pending' => 1,
                'total' => 2,
            ]
        ]);
    }

    /**
     * @before loadDataByTahap
     */
    public function getStaffKelDashboardFilterByTahap(ApiTester $I)
    {
        $I->amStaff('staffkel');

        $I->sendGET("{$this->endpointBeneficiaries}/approval-dashboard?tahap=1");
        $I->canSeeResponseCodeIs(200);

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
            'data' => [
                'approved' => 0,
                'rejected' => 0,
                'pending' => 1,
                'total' => 1,
            ]
        ]);

        $I->sendGET("{$this->endpointBeneficiaries}/approval-dashboard?tahap=2");
        $I->canSeeResponseCodeIs(200);

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
            'data' => [
                'approved' => 1,
                'rejected' => 0,
                'pending' => 1,
                'total' => 2,
            ]
        ]);
    }

    /**
     * @before loadData
     */
    public function postStaffKelApproval(ApiTester $I)
    {
        $data = [
            'action' => Beneficiary::ACTION_APPROVE,
        ];

        $I->amStaff('staffkel');

        // Action = 'APPROVE'
        $I->sendPOST("{$this->endpointBeneficiaries}/approval/1", $data);
        $I->canSeeResponseCodeIs(200);

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeInDatabase('beneficiaries', [
            'id' => 1,
            'status_verification' => Beneficiary::STATUS_APPROVED_KEL,
        ]);

        // Action = 'REJECT'
        $data['action'] = Beneficiary::ACTION_REJECT;
        $I->sendPOST("{$this->endpointBeneficiaries}/approval/1", $data);
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

    /**
     * @before loadData
     */
    public function postStaffKelBulkApproval(ApiTester $I)
    {
        $data = [
            'action' => Beneficiary::ACTION_APPROVE,
            'ids' => [
                1,
                2,
            ],
        ];

        $I->amStaff('staffkel');

        // Action = 'APPROVE'
        $I->sendPOST("{$this->endpointBeneficiaries}/bulk-approval", $data);
        $I->canSeeResponseCodeIs(200);

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeInDatabase('beneficiaries', [
            'id' => 1,
            'status_verification' => Beneficiary::STATUS_APPROVED_KEL,
        ]);

        $I->seeInDatabase('beneficiaries', [
            'id' => 2,
            'status_verification' => Beneficiary::STATUS_APPROVED_KEL,
        ]);

        // Action = 'REJECT'
        $data['action'] = Beneficiary::ACTION_REJECT;
        $I->sendPOST("{$this->endpointBeneficiaries}/bulk-approval", $data);
        $I->canSeeResponseCodeIs(200);

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeInDatabase('beneficiaries', [
            'id' => 1,
            'status_verification' => Beneficiary::STATUS_REJECTED_KEL,
        ]);

        $I->seeInDatabase('beneficiaries', [
            'id' => 2,
            'status_verification' => Beneficiary::STATUS_REJECTED_KEL,
        ]);
    }
}
