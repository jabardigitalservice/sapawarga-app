<?php

class BroadcastCest
{
    private $endpointBroadcast = '/v1/broadcasts';

    public function _before(ApiTester $I)
    {
        Yii::$app->db->createCommand()->checkIntegrity(false)->execute();

        Yii::$app->db->createCommand('TRUNCATE broadcasts')->execute();
        Yii::$app->db->createCommand('TRUNCATE user_messages')->execute();
    }

    private function addNewBroadcast(ApiTester $I, $id, $area) {
        $I->haveInDatabase('broadcasts', [
            'id'          => $id,
            'author_id'   => 1,
            'category_id' => 5,
            'title'       => 'Broadcast Title',
            'description' => 'Broadcast Description',
            'kabkota_id'  => $area['kabkota_id'],
            'kec_id'      => $area['kec_id'],
            'kel_id'      => $area['kel_id'],
            'rw'          => $area['rw'],
            'status'      => 10,
            'created_at'  => time(),
            'updated_at'  => time(),
        ]);
    }

    // Test cases for admins
    public function staffProvCanCreateBroadcast(ApiTester $I)
    {
        $I->amStaff('staffprov');

        $I->sendPOST('/v1/broadcasts?test=1', [
            'category_id'  => 5,
            'title'        => 'Broadcast Title',
            'description'  => 'Broadcast Description',
            'kabkota_id'   => null,
            'kec_id'       => null,
            'kel_id'       => null,
            'rw'           => null,
            'status'       => 10,
        ]);

        $I->canSeeResponseCodeIs(201);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 201,
        ]);

        $I->seeInDatabase('broadcasts', [
            'author_id'    => 2,
            'category_id'  => 5,
            'title'        => 'Broadcast Title',
            'description'  => 'Broadcast Description',
            'is_scheduled' => false,
            'kabkota_id'   => null,
            'kec_id'       => null,
            'kel_id'       => null,
            'rw'           => null,
            'status'       => 10,
        ]);
    }

    public function staffKabkotaCanCreateBroadcast(ApiTester $I)
    {
        $I->amStaff('staffkabkota');

        $I->sendPOST('/v1/broadcasts?test=1', [
            'category_id'  => 5,
            'title'        => 'Broadcast Title',
            'description'  => 'Broadcast Description',
            'kabkota_id'   => 22,
            'kec_id'       => null,
            'kel_id'       => null,
            'rw'           => null,
            'status'       => 10,
        ]);

        $I->canSeeResponseCodeIs(201);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 201,
        ]);

        $I->seeInDatabase('broadcasts', [
            'author_id'    => 3,
            'category_id'  => 5,
            'title'        => 'Broadcast Title',
            'description'  => 'Broadcast Description',
            'kabkota_id'   => 22,
            'kec_id'       => null,
            'kel_id'       => null,
            'rw'           => null,
            'status'       => 10,
        ]);
    }

    public function staffKecamatanCanCreateBroadcast(ApiTester $I)
    {
        $I->amStaff('staffkec');

        $I->sendPOST('/v1/broadcasts?test=1', [
            'category_id'  => 5,
            'title'        => 'Broadcast Title',
            'description'  => 'Broadcast Description',
            'kabkota_id'   => 22,
            'kec_id'       => 431,
            'kel_id'       => null,
            'rw'           => null,
            'status'       => 10,
        ]);

        $I->canSeeResponseCodeIs(201);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 201,
        ]);

        $I->seeInDatabase('broadcasts', [
            'author_id'    => 5,
            'category_id'  => 5,
            'title'        => 'Broadcast Title',
            'description'  => 'Broadcast Description',
            'is_scheduled' => false,
            'kabkota_id'   => 22,
            'kec_id'       => 431,
            'kel_id'       => null,
            'rw'           => null,
            'status'       => 10,
        ]);
    }

    public function staffKelurahanCanCreateBroadcast(ApiTester $I)
    {
        $I->amStaff('staffkel');

        $I->sendPOST('/v1/broadcasts?test=1', [
            'category_id'  => 5,
            'title'        => 'Broadcast Title',
            'description'  => 'Broadcast Description',
            'kabkota_id'   => 22,
            'kec_id'       => 431,
            'kel_id'       => 6093,
            'rw'           => null,
            'status'       => 10,
        ]);

        $I->canSeeResponseCodeIs(201);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 201,
        ]);

        $I->seeInDatabase('broadcasts', [
            'author_id'    => 9,
            'category_id'  => 5,
            'title'        => 'Broadcast Title',
            'description'  => 'Broadcast Description',
            'is_scheduled' => false,
            'kabkota_id'   => 22,
            'kec_id'       => 431,
            'kel_id'       => 6093,
            'rw'           => null,
            'status'       => 10,
        ]);
    }

    public function staffCanCreateScheduledBroadcast(ApiTester $I)
    {
        $I->amStaff('staffprov');

        $scheduledDatetime = time() + 3600;

        $I->sendPOST('/v1/broadcasts', [
            'category_id'  => 5,
            'title'        => 'Broadcast Title',
            'description'  => 'Broadcast Description',
            'is_scheduled' => true,
            'scheduled_datetime' => $scheduledDatetime,
            'kabkota_id'   => 22,
            'kec_id'       => null,
            'kel_id'       => null,
            'rw'           => null,
            'status'       => 10,
        ]);

        $I->canSeeResponseCodeIs(201);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 201,
        ]);

        $I->seeInDatabase('broadcasts', [
            'author_id'    => 2,
            'category_id'  => 5,
            'title'        => 'Broadcast Title',
            'description'  => 'Broadcast Description',
            'is_scheduled' => true,
            'scheduled_datetime' => $scheduledDatetime,
            'kabkota_id'   => 22,
            'kec_id'       => null,
            'kel_id'       => null,
            'rw'           => null,
            'status'       => 5,
        ]);
    }

    public function staffCanCreateDraftScheduledBroadcast(ApiTester $I)
    {
        $I->amStaff('staffprov');

        $scheduledDatetime = time() + 3600;

        $I->sendPOST('/v1/broadcasts', [
            'category_id'  => 5,
            'title'        => 'Broadcast Title',
            'description'  => 'Broadcast Description',
            'is_scheduled' => true,
            'scheduled_datetime' => $scheduledDatetime,
            'kabkota_id'   => 22,
            'kec_id'       => null,
            'kel_id'       => null,
            'rw'           => null,
            'status'       => 0,
        ]);

        $I->canSeeResponseCodeIs(201);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 201,
        ]);


        $I->seeInDatabase('broadcasts', [
            'author_id'    => 2,
            'category_id'  => 5,
            'title'        => 'Broadcast Title',
            'description'  => 'Broadcast Description',
            'is_scheduled' => true,
            'scheduled_datetime' => $scheduledDatetime,
            'kabkota_id'   => 22,
            'kec_id'       => null,
            'kel_id'       => null,
            'rw'           => null,
            'status'       => 0,
        ]);
    }

    public function userCannotCreateNewTest(ApiTester $I)
    {
        $I->amUser();

        $I->sendPOST($this->endpointBroadcast);
        $I->canSeeResponseCodeIs(403);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => false,
            'status'  => 403,
        ]);
    }

    public function userCannotUpdateTest(ApiTester $I)
    {
        $I->amUser();

        $I->sendPUT("{$this->endpointBroadcast}/0");
        $I->canSeeResponseCodeIs(403);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => false,
            'status'  => 403,
        ]);
    }

    public function userCannotDeleteTest(ApiTester $I)
    {
        $I->amUser();

        $I->sendDELETE("{$this->endpointBroadcast}/0");
        $I->canSeeResponseCodeIs(403);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => false,
            'status'  => 403,
        ]);
    }

    public function updateBroadcast(ApiTester $I)
    {
        $I->haveInDatabase('broadcasts', [
            'id'           => 1,
            'category_id'  => 5,
            'author_id'    => 1,
            'title'        => 'Kegiatan Gubernur.',
            'description'  => 'Lorem ipsum.',
            'is_scheduled' => false,
            'kabkota_id'   => 22,
            'status'       => 10,
            'created_at'   => '1554706345',
            'updated_at'   => '1554706345',
        ]);

        $I->amStaff();

        $I->sendPUT("{$this->endpointBroadcast}/1?test=1", [
            'title' => 'Edited',
        ]);

        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);
    }

    public function deleteBroadcast(ApiTester $I)
    {
        $I->haveInDatabase('broadcasts', [
            'id'           => 1,
            'category_id'  => 5,
            'author_id'    => 1,
            'title'        => 'Kegiatan Gubernur.',
            'description'  => 'Lorem ipsum.',
            'is_scheduled' => false,
            'kabkota_id'   => 22,
            'status'       => 10,
            'created_at'   => '1554706345',
            'updated_at'   => '1554706345',
        ]);

        $I->amStaff();

        $I->sendDELETE("{$this->endpointBroadcast}/1?test=1");
        $I->canSeeResponseCodeIs(204);
    }

    public function createNewBroadcastCategoryInvalid(ApiTester $I)
    {
        $I->amStaff();

        $I->sendPOST($this->endpointBroadcast, [
            'author_id'    => 1,
            'category_id'  => 0,
            'title'        => 'Broadcast Title',
            'description'  => 'Broadcast Description',
            'is_scheduled' => false,
            'kabkota_id'   => null,
            'kec_id'       => null,
            'kel_id'       => null,
            'rw'           => null,
            'status'       => 10,
        ]);

        $I->canSeeResponseCodeIs(422);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => false,
            'status'  => 422,
        ]);
    }

    public function getBroadcastListAll(ApiTester $I)
    {
        $I->haveInDatabase('broadcasts', [
            'id'           => 1,
            'category_id'  => 5,
            'author_id'    => 1,
            'title'        => 'Lorem.',
            'description'  => 'Lorem ipsum.',
            'is_scheduled' => false,
            'kabkota_id'   => 22,
            'status'       => 10,
            'created_at'   => '1554706345',
            'updated_at'   => '1554706345',
        ]);

        $I->haveInDatabase('broadcasts', [
            'id'           => 2,
            'category_id'  => 5,
            'author_id'    => 1,
            'title'        => 'Lorem.',
            'description'  => 'Lorem ipsum.',
            'is_scheduled' => false,
            'kabkota_id'   => 23,
            'status'       => 10,
            'created_at'   => '1554706345',
            'updated_at'   => '1554706345',
        ]);

        $I->amStaff();

        $I->sendGET($this->endpointBroadcast);
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeResponseContainsJson([
            'kabkota_id' => 22,
        ]);

        $I->seeResponseContainsJson([
            'kabkota_id' => 23,
        ]);
    }

    public function getBroadcastListFilterCategory(ApiTester $I)
    {
        $I->haveInDatabase('broadcasts', [
            'id'          => 1,
            'category_id' => 5,
            'author_id'   => 1,
            'title'       => 'Lorem.',
            'description' => 'Lorem ipsum.',
            'kabkota_id'  => 22,
            'status'      => 10,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->haveInDatabase('broadcasts', [
            'id'          => 2,
            'category_id' => 6,
            'author_id'   => 1,
            'title'       => 'Lorem.',
            'description' => 'Lorem ipsum.',
            'kabkota_id'  => 23,
            'status'      => 10,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->haveInDatabase('broadcasts', [
            'id'          => 3,
            'category_id' => 7,
            'author_id'   => 1,
            'title'       => 'Lorem.',
            'description' => 'Lorem ipsum.',
            'kabkota_id'  => 23,
            'status'      => 10,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->amStaff();

        $I->sendGET("{$this->endpointBroadcast}?category_id=5");
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeResponseContainsJson([
            'category_id' => 5,
        ]);

        $I->cantSeeResponseContainsJson([
            'category_id' => 6,
        ]);

        $I->cantSeeResponseContainsJson([
            'category_id' => 7,
        ]);
    }

    public function getBroadcastListFilterStatus(ApiTester $I)
    {
        $I->haveInDatabase('broadcasts', [
            'id'          => 1,
            'category_id' => 5,
            'author_id'   => 1,
            'title'       => 'Lorem.',
            'description' => 'Lorem ipsum.',
            'kabkota_id'  => 22,
            'status'      => 10,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->haveInDatabase('broadcasts', [
            'id'          => 2,
            'category_id' => 5,
            'author_id'   => 1,
            'title'       => 'Lorem.',
            'description' => 'Lorem ipsum.',
            'kabkota_id'  => 22,
            'status'      => 0,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->amStaff();

        $I->sendGET("{$this->endpointBroadcast}?status=10");
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeResponseContainsJson([
            'data' => [
                'items' => [
                    [
                        'status' => 10,
                    ],
                ],
            ],
        ]);

        $I->cantSeeResponseContainsJson([
            'data' => [
                'items' => [
                    [
                        'status' => 0,
                    ],
                ],
            ],
        ]);
    }

    public function getBroadcastListSearchTitle(ApiTester $I)
    {
        $I->haveInDatabase('broadcasts', [
            'id'          => 1,
            'category_id' => 5,
            'author_id'   => 1,
            'title'       => 'Kegiatan Gubernur.',
            'description' => 'Lorem ipsum.',
            'kabkota_id'  => 22,
            'status'      => 10,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->haveInDatabase('broadcasts', [
            'id'          => 2,
            'category_id' => 6,
            'author_id'   => 1,
            'title'       => 'Lorem.',
            'description' => 'Lorem ipsum.',
            'kabkota_id'  => 22,
            'status'      => 10,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->amStaff('staffkabkota');

        $I->sendGET("{$this->endpointBroadcast}?title=Kegiatan");
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeHttpHeader('X-Pagination-Total-Count', 1);

        $data = $I->grabDataFromResponseByJsonPath('$.data.items[0]');

        $I->assertEquals(1, $data[0]['id']);
    }

    public function getBroadcastListAllCannotSeeOtherUserDraft(ApiTester $I)
    {
        $I->haveInDatabase('broadcasts', [
            'id'          => 1,
            'category_id' => 5,
            'author_id'   => 2,
            'title'       => 'Lorem.',
            'description' => 'Lorem ipsum.',
            'kabkota_id'  => 22,
            'status'      => 0,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->amStaff();

        $I->sendGET($this->endpointBroadcast);
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $data = $I->grabDataFromResponseByJsonPath('$.data.items[0]');
        $I->assertEquals(0, count($data));


        $I->amStaff('staffprov');

        $I->sendGET($this->endpointBroadcast);
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $data = $I->grabDataFromResponseByJsonPath('$.data.items[0]');
        $I->assertEquals(1, count($data));
        $I->assertEquals(1, $data[0]['id']);
    }

    public function getBroadcastItemNotFound(ApiTester $I)
    {
        $I->amStaff();

        $I->sendGET("{$this->endpointBroadcast}/0");
        $I->canSeeResponseCodeIs(404);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => false,
            'status'  => 404,
        ]);
    }

    public function getBroadcastItem(ApiTester $I)
    {
        $I->haveInDatabase('broadcasts', [
            'id'          => 1,
            'category_id' => 5,
            'author_id'   => 1,
            'title'       => 'Kegiatan Gubernur.',
            'description' => 'Lorem ipsum.',
            'kabkota_id'  => 22,
            'status'      => 10,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->amStaff();

        $I->sendGET("{$this->endpointBroadcast}/1");
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
            'data'    => [
                'id' => 1,
            ],
        ]);
    }

    public function processScheduledBroadcast(ApiTester $I)
    {
        $I->haveInDatabase('broadcasts', [
            'id'                 => 1,
            'category_id'        => 5,
            'author_id'          => 1,
            'title'              => 'Kegiatan Gubernur.',
            'description'        => 'Lorem ipsum.',
            'kabkota_id'         => 22,
            'status'             => 0, // DRAFT
            'scheduled_datetime' => null,
            'is_scheduled'       => false,
            'created_at'         => '1554706345',
            'updated_at'         => '1554706345',
        ]);

        $I->haveInDatabase('broadcasts', [
            'id'                 => 2,
            'category_id'        => 5,
            'author_id'          => 1,
            'title'              => 'Kegiatan Gubernur.',
            'description'        => 'Lorem ipsum.',
            'kabkota_id'         => 22,
            'status'             => 10, // PUBLISHED
            'scheduled_datetime' => null,
            'is_scheduled'       => false,
            'created_at'         => '1554706345',
            'updated_at'         => '1554706345',
        ]);

        $I->haveInDatabase('broadcasts', [
            'id'                 => 3,
            'category_id'        => 5,
            'author_id'          => 1,
            'title'              => 'Kegiatan Gubernur.',
            'description'        => 'Lorem ipsum.',
            'kabkota_id'         => 22,
            'status'             => 5, // SCHEDULED
            'scheduled_datetime' => time() + 3600,
            'is_scheduled'       => true,
            'created_at'         => '1554706345',
            'updated_at'         => '1554706345',
        ]);

        $I->haveInDatabase('broadcasts', [
            'id'                 => 4,
            'category_id'        => 5,
            'author_id'          => 1,
            'title'              => 'Kegiatan Gubernur.',
            'description'        => 'Lorem ipsum.',
            'kabkota_id'         => 22,
            'status'             => 5, // SCHEDULED
            'scheduled_datetime' => time() - 3600,
            'is_scheduled'       => true,
            'created_at'         => '1554706345',
            'updated_at'         => '1554706345',
        ]);

        $I->amStaff();

        $I->sendGET('/v1/cron/broadcasts');
        $I->canSeeResponseCodeIs(200);

        // Keep in scheduled, because schedule datetime is not due
        $I->seeInDatabase('broadcasts', [
            'id'        => 3,
            'status'    => 5,
        ]);

        // Published, because scheduled datetime is due
        $I->seeInDatabase('broadcasts', [
            'id'        => 4,
            'status'    => 10,
        ]);
    }

    public function _after(ApiTester $I)
    {
        Yii::$app->db->createCommand()->checkIntegrity(false)->execute();

        Yii::$app->db->createCommand('TRUNCATE broadcasts')->execute();
        Yii::$app->db->createCommand('TRUNCATE user_messages')->execute();
    }
}
