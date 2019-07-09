<?php

class NewsChannelCest
{
    private $endpointNewsChannel = '/v1/news-channels';

    public function createNewNewsChannelNameExist(ApiTester $I)
    {
        $I->amStaff();

        $I->sendPOST($this->endpointNewsChannel, [
            'name'      => 'Detik',
            'status'    => 10,
        ]);

        $I->canSeeResponseCodeIs(422);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => false,
            'status'  => 422,
        ]);
    }

    public function createNewNewsChannel(ApiTester $I)
    {
        $I->amStaff();

        $I->sendPOST($this->endpointNewsChannel, [
            'name'      => 'Detik',
            'status'    => 10,
        ]);

        $I->canSeeResponseCodeIs(201);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 201,
        ]);
    }

    public function getNewsChannelListAll(ApiTester $I)
    {
        $I->amStaff();

        $I->sendGET($this->endpointNewsChannel);
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeResponseContainsJson([
            'name' => 'Detik',
        ]);

        $I->seeResponseContainsJson([
            'name' => 'Kompas',
        ]);
    }

    public function getNewsChannelItemNotFound(ApiTester $I)
    {
        $I->amStaff();

        $I->sendGET("{$this->endpointNewsChannel}/999");
        $I->canSeeResponseCodeIs(404);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => false,
            'status'  => 404,
        ]);
    }

    public function getNewsChannelItem(ApiTester $I)
    {
        $I->amStaff();

        $I->sendGET("{$this->endpointNewsChannel}/1");
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success'   => true,
            'status'    => 200,
            'data'      => [
                'id' => 1,
                'name' => 'Detik',
                'status' => 10,
            ]
        ]);
    }

    public function updateNewsChannelNameExist(ApiTester $I)
    {

    }

    public function updateNewsChannel(ApiTester $I)
    {
        $I->amStaff();

        $I->sendPUT("{$this->endpointNewsChannel}/1", [
            'name' => 'Detik Edited',
        ]);

        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);
    }

    public function deleteNewsChannel(ApiTester $I)
    {
        $I->amStaff();

        $I->sendDELETE("{$this->endpointNewsChannel}/1");
        $I->canSeeResponseCodeIs(204);
    }
}
