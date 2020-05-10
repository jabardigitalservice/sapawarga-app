<?php

use app\models\Beneficiary;

class BeneficiaryApprovalCest
{
    private $endpointBeneficiaries = '/v1/beneficiaries';

    protected function loadData(ApiTester $I)
    {
        $I->haveInDatabase('beneficiaries', [
            'id' => 1,
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
            'id' => [1],
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
            'status_verifcation' => Beneficiary::STATUS_APPROVED_KEL,
        ]);
    }
}
