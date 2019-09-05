<?php

class ReleaseCest
{
    private $endpointRelease = '/v1/releases';

    public function createNewReleaseVersionExist(ApiTester $I)
    {
        $I->amStaff();

        $I->haveInDatabase('releases', [
            'id'         => 1,
            'version'    => '1.0.0',
            'force_update'    => true,
        ]);

        $I->sendPOST($this->endpointRelease, [
            'version'    => '1.0.0',
            'force_update'    => true,
        ]);

        $I->canSeeResponseCodeIs(422);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => false,
            'status'  => 422,
        ]);
    }

    public function createNewRelease(ApiTester $I)
    {
        $I->amStaff();

        $I->sendPOST($this->endpointRelease, [
            'version'    => '1.0.1',
            'force_update'    => false,
        ]);

        $I->canSeeResponseCodeIs(201);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 201,
        ]);
    }

    public function getReleaseListAll(ApiTester $I)
    {
        $I->amStaff();

        $I->sendGET($this->endpointRelease);
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);
    }

    public function getReleaseItemNotFound(ApiTester $I)
    {
        $I->amStaff();

        $I->sendGET("{$this->endpointRelease}/999");
        $I->canSeeResponseCodeIs(404);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => false,
            'status'  => 404,
        ]);
    }

    public function getReleaseItem(ApiTester $I)
    {
        $I->amStaff();

        $I->sendGET("{$this->endpointRelease}/1");
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success'   => true,
            'status'    => 200,
            'data'      => [
                'id' => 1,
                'version' => '1.0.0',
                'force_update' => true,
            ]
        ]);
    }

    public function updateRelease(ApiTester $I)
    {
        $I->amStaff();

        $I->sendPUT("{$this->endpointRelease}/1", [
            'version' => '1.0.2',
        ]);

        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);
    }

    public function deleteRelease(ApiTester $I)
    {
        $I->amStaff();

        $I->sendDELETE("{$this->endpointRelease}/1");
        $I->canSeeResponseCodeIs(204);
    }
}
