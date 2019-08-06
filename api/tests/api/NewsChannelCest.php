<?php

class NewsChannelCest
{
    private $endpointNewsChannel = '/v1/news-channels';

    public function createNewNewsChannelNameExist(ApiTester $I)
    {
        Yii::$app->db->createCommand()->checkIntegrity(false)->execute();
        Yii::$app->db->createCommand('TRUNCATE news')->execute();
        Yii::$app->db->createCommand('TRUNCATE news_channels')->execute();

        $I->haveInDatabase('news_channels', [
            'id'         => 1,
            'name'       => 'Detik',
            'status'     => 10,
        ]);


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

    public function createNewNewsChannelWebsiteExist(ApiTester $I)
    {
        Yii::$app->db->createCommand()->checkIntegrity(false)->execute();
        Yii::$app->db->createCommand('TRUNCATE news')->execute();
        Yii::$app->db->createCommand('TRUNCATE news_channels')->execute();

        $I->haveInDatabase('news_channels', [
            'id'         => 1,
            'name'       => 'Detik',
            'website'    => 'https://www.detik.com',
            'status'     => 10,
        ]);


        $I->amStaff();
        $I->sendPOST($this->endpointNewsChannel, [
            'name'      => 'Detik2',
            'website'    => 'https://www.detik.com',
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
            'name'      => 'Kompas',
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
        Yii::$app->db->createCommand()->checkIntegrity(false)->execute();
        Yii::$app->db->createCommand('TRUNCATE news')->execute();
        Yii::$app->db->createCommand('TRUNCATE news_channels')->execute();

        $I->amStaff();

        $I->sendPOST($this->endpointNewsChannel, [
            'id'        => 1,
            'name'      => 'Detik',
            'status'    => 10,
        ]);
        $I->sendPOST($this->endpointNewsChannel, [
            'id'        => 2,
            'name'      => 'Kompas',
            'status'    => 10,
        ]);

        $I->sendGET($this->endpointNewsChannel);
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $data = $I->grabDataFromResponseByJsonPath('$.data.items');
        $I->assertEquals('Detik', $data[0][0]['name']);
        $I->assertEquals('Kompas', $data[0][1]['name']);
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
        $I->amStaff();

        $I->sendPUT ("{$this->endpointNewsChannel}/1", [
            'name' => 'Kompas',
        ]);

        $I->canSeeResponseCodeIs(422);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => false,
            'status'  => 422,
        ]);
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

        $I->seeInDatabase('news_channels', [
            'id'        => 1,
            'name'      => 'Detik Edited',
            'status'    => 10,
        ]);
    }

    public function deleteNewsChannel(ApiTester $I)
    {
        $I->amStaff();

        $I->sendDELETE("{$this->endpointNewsChannel}/1");
        $I->canSeeResponseCodeIs(204);
    }
}
