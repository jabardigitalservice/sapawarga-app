<?php

class ReleaseCest
{
    private $endpointRelease = '/v1/releases';

    public function postCreateReleaseUnauthorized(ApiTester $I)
    {
        $I->amStaff('staffprov');
        $data = [];
        $I->sendPOST($this->endpointRelease, $data);
        $I->canSeeResponseCodeIs(403);
        $I->seeResponseIsJson();

        $I->amUser('staffrw');
        $data = [];
        $I->sendPOST($this->endpointRelease, $data);
        $I->canSeeResponseCodeIs(403);
        $I->seeResponseIsJson();
    }

    public function createNewReleaseVersionExist(ApiTester $I)
    {
        $I->amStaff();

        $I->sendPOST($this->endpointRelease, [
            'version'    => '0.0.1',
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

        $I->seeInDatabase('releases', [
            'version'       => '1.0.1',
            'force_update'  => false,
        ]);
    }

    public function getReleaseUnauthorized(ApiTester $I)
    {
        $I->amStaff('staffprov');
        $I->sendGET($this->endpointRelease);
        $I->canSeeResponseCodeIs(403);

        $I->amUser('staffrw');
        $I->sendGET($this->endpointRelease);
        $I->canSeeResponseCodeIs(403);
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
    }

    public function postUpdateReleaseUnauthorized(ApiTester $I)
    {
        $I->amStaff('staffprov');
        $data = [];
        $I->sendPUT("{$this->endpointRelease}/1", $data);
        $I->canSeeResponseCodeIs(403);
        $I->seeResponseIsJson();

        $I->amUser('staffrw');
        $data = [];
        $I->sendPUT("{$this->endpointRelease}/1", $data);
        $I->canSeeResponseCodeIs(403);
        $I->seeResponseIsJson();
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

    public function deleteReleaseUnauthorizedTest(ApiTester $I)
    {
        $I->amStaff('staffprov');
        $I->sendDELETE("{$this->endpointRelease}/1");
        $I->canSeeResponseCodeIs(403);

        $I->amUser('staffrw');
        $I->sendDELETE("{$this->endpointRelease}/1");
        $I->canSeeResponseCodeIs(403);
    }

    public function deleteRelease(ApiTester $I)
    {
        $I->amStaff();

        $I->sendDELETE("{$this->endpointRelease}/1");
        $I->canSeeResponseCodeIs(204);
    }
}
