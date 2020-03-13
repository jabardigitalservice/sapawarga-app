<?php

use Carbon\Carbon;

class SurveyCest
{
    public function _before(ApiTester $I)
    {
        Yii::$app->db->createCommand()->checkIntegrity(false)->execute();

        Yii::$app->db->createCommand('TRUNCATE survey')->execute();
    }

    public function getListUnauthorizedTest(ApiTester $I)
    {
        $I->amStaff('staffkec');
        $I->sendGET('/v1/survey');
        $I->canSeeResponseCodeIs(403);

        $I->amStaff('staffkel');
        $I->sendGET('/v1/survey');
        $I->canSeeResponseCodeIs(403);
    }

    public function getListTest(ApiTester $I)
    {
        $I->haveInDatabase('survey', [
            'id'           => 1,
            'title'        => 'Lorem ipsum.',
            'status'       => 10,
            'category_id'  => 20,
            'start_date'   => (new Carbon())->toDateString(),
            'end_date'     => (new Carbon())->addDays(7)->toDateString(),
            'external_url' => 'http://google.com',
            'created_by'   => 1,
            'updated_by'   => 1,
        ]);

        $I->haveInDatabase('survey', [
            'id'           => 2,
            'title'        => 'Lorem ipsum.',
            'status'       => 10,
            'category_id'  => 20,
            'kabkota_id'   => 23,
            'start_date'   => (new Carbon())->toDateString(),
            'end_date'     => (new Carbon())->addDays(7)->toDateString(),
            'external_url' => 'http://google.com',
            'created_by'   => 1,
            'updated_by'   => 1,
        ]);

        $I->haveInDatabase('survey', [
            'id'           => 3,
            'title'        => 'Lorem ipsum.',
            'status'       => 10,
            'category_id'  => 20,
            'kabkota_id'   => 22,
            'start_date'   => (new Carbon())->toDateString(),
            'end_date'     => (new Carbon())->addDays(7)->toDateString(),
            'external_url' => 'http://google.com',
            'created_by'   => 1,
            'updated_by'   => 1,
        ]);

        // staffrw
        $I->amUser('staffrw');

        $I->sendGET('/v1/survey');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeHttpHeader('X-Pagination-Total-Count', 2);

        $data = $I->grabDataFromResponseByJsonPath('$.data.items');
        $I->assertEquals(1, $data[0][0]['id']);
        $I->assertEquals(3, $data[0][1]['id']);

        //pimpinan
        $I->amStaff('gubernur');

        $I->sendGET('/v1/survey');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeHttpHeader('X-Pagination-Total-Count', 3);

        $data = $I->grabDataFromResponseByJsonPath('$.data.items');

        $I->assertEquals(1, $data[0][0]['id']);
        $I->assertEquals(2, $data[0][1]['id']);
        $I->assertEquals(3, $data[0][2]['id']);

        //staffprov
        $I->amStaff('staffprov');

        $I->sendGET('/v1/survey');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeHttpHeader('X-Pagination-Total-Count', 3);

        $data = $I->grabDataFromResponseByJsonPath('$.data.items');

        $I->assertEquals(1, $data[0][0]['id']);
        $I->assertEquals(2, $data[0][1]['id']);
        $I->assertEquals(3, $data[0][2]['id']);

        // staffkabkota
        $I->amStaff('staffkabkota2');

        $I->sendGET('/v1/survey');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeHttpHeader('X-Pagination-Total-Count', 2);

        $data = $I->grabDataFromResponseByJsonPath('$.data.items');
        $I->assertEquals(1, $data[0][0]['id']);
        $I->assertEquals(2, $data[0][1]['id']);
    }

    public function getUserListPublishedShowTest(ApiTester $I)
    {
        $I->haveInDatabase('survey', [
            'id'           => 1,
            'title'        => 'Lorem ipsum.',
            'status'       => 10,
            'category_id'  => 20,
            'start_date'   => (new Carbon())->toDateString(),
            'end_date'     => (new Carbon())->addDays(7)->toDateString(),
            'external_url' => 'http://google.com',
            'created_by'   => 1,
            'updated_by'   => 1,
            'created_at'   => '1554706345',
            'updated_at'   => '1554706345',
        ]);

        $I->amUser('staffrw');

        $I->sendGET('/v1/survey');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $data = $I->grabDataFromResponseByJsonPath('$.data.items[0]');

        $I->assertEquals(1, $data[0]['id']);
    }

    public function getAdminListPublishedShowTest(ApiTester $I)
    {
        $I->haveInDatabase('survey', [
            'id'           => 1,
            'title'        => 'Lorem ipsum.',
            'status'       => 10,
            'category_id'  => 20,
            'start_date'   => (new Carbon())->toDateString(),
            'end_date'     => (new Carbon())->addDays(7)->toDateString(),
            'external_url' => 'http://google.com',
            'created_by'   => 1,
            'updated_by'   => 1,
            'created_at'   => '1554706345',
            'updated_at'   => '1554706345',
        ]);

        $I->amStaff();

        $I->sendGET('/v1/survey');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $data = $I->grabDataFromResponseByJsonPath('$.data.items[0]');

        $I->assertEquals(1, $data[0]['id']);
    }

    public function getAdminListSearchTitleTest(ApiTester $I)
    {
        $I->haveInDatabase('survey', [
            'id'           => 1,
            'title'        => 'Jajak Pendapat',
            'status'       => 0,
            'category_id'  => 20,
            'external_url' => 'http://google.com',
            'created_by'   => 1,
            'updated_by'   => 1,
            'created_at'   => '1554706345',
            'updated_at'   => '1554706345',
        ]);

        $I->haveInDatabase('survey', [
            'id'           => 2,
            'title'        => 'Survei',
            'status'       => 0,
            'category_id'  => 20,
            'external_url' => 'http://google.com',
            'created_by'   => 1,
            'updated_by'   => 1,
            'created_at'   => '1554706345',
            'updated_at'   => '1554706345',
        ]);

        $I->amStaff();

        $I->sendGET('/v1/survey?title=Survei');
        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeResponseContainsJson([
            'title' => 'Survei',
        ]);

        $I->cantSeeResponseContainsJson([
            'title' => 'Jajak Pendapat',
        ]);
    }

    public function getAdminListFilterStatusTest(ApiTester $I)
    {
        $I->haveInDatabase('survey', [
            'id'           => 1,
            'title'        => 'Survei',
            'status'       => 1,
            'category_id'  => 20,
            'external_url' => 'http://google.com',
            'created_by'   => 1,
            'updated_by'   => 1,
            'created_at'   => '1554706345',
            'updated_at'   => '1554706345',
        ]);

        $I->haveInDatabase('survey', [
            'id'           => 2,
            'title'        => 'Survei',
            'status'       => 10,
            'category_id'  => 20,
            'external_url' => 'http://google.com',
            'created_by'   => 1,
            'updated_by'   => 1,
            'created_at'   => '1554706345',
            'updated_at'   => '1554706345',
        ]);

        $I->amStaff();

        $I->sendGET('/v1/survey?status=10');
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
                        'status' => 1,
                    ],
                ],
            ],
        ]);
    }

    public function getAdminListFilterCategoryTest(ApiTester $I)
    {
        $I->haveInDatabase('survey', [
            'id'           => 1,
            'title'        => 'Survei',
            'status'       => 10,
            'category_id'  => 20,
            'external_url' => 'http://google.com',
            'created_by'   => 1,
            'updated_by'   => 1,
            'created_at'   => '1554706345',
            'updated_at'   => '1554706345',
        ]);

        $I->haveInDatabase('survey', [
            'id'           => 2,
            'title'        => 'Survei',
            'status'       => 10,
            'category_id'  => 21,
            'external_url' => 'http://google.com',
            'created_by'   => 1,
            'updated_by'   => 1,
            'created_at'   => '1554706345',
            'updated_at'   => '1554706345',
        ]);

        $I->amStaff();

        $I->sendGET('/v1/survey?category_id=20');
        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeResponseContainsJson([
            'category_id' => '20',
        ]);

        $I->cantSeeResponseContainsJson([
            'category_id' => '21',
        ]);
    }

    public function getAdminListFilterAreaTest(ApiTester $I)
    {
        $I->haveInDatabase('survey', [
            'id'           => 1,
            'title'        => 'Survei',
            'status'       => 10,
            'category_id'  => 20,
            'external_url' => 'http://google.com',
            'kabkota_id'   => 22,
            'created_by'   => 1,
            'updated_by'   => 1,
            'created_at'   => '1554706345',
            'updated_at'   => '1554706345',
        ]);

        $I->haveInDatabase('survey', [
            'id'           => 2,
            'title'        => 'Survei',
            'status'       => 10,
            'category_id'  => 20,
            'external_url' => 'http://google.com',
            'kabkota_id'   => 23,
            'created_by'   => 1,
            'updated_by'   => 1,
            'created_at'   => '1554706345',
            'updated_at'   => '1554706345',
        ]);

        $I->amStaff();

        $I->sendGET('/v1/survey?kabkota_id=22');
        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeResponseContainsJson([
            'data' => [ 'items' => [ [ 'kabkota_id' => 22, ], ], ],
        ]);

        $I->cantSeeResponseContainsJson([
            'data' => [ 'items' => [ [ 'kabkota_id' => 23, ], ],
            ],
        ]);
    }


    public function getUserListDeletedDontShowTest(ApiTester $I)
    {
        $I->haveInDatabase('survey', [
            'id'           => 1,
            'title'        => 'Lorem ipsum.',
            'status'       => -1,
            'category_id'  => 20,
            'start_date'   => (new Carbon())->toDateString(),
            'end_date'     => (new Carbon())->addDays(7)->toDateString(),
            'external_url' => 'http://google.com',
            'created_by'   => 1,
            'updated_by'   => 1,
            'created_at'   => '1554706345',
            'updated_at'   => '1554706345',
        ]);

        $I->amUser('staffrw');

        $I->sendGET('/v1/survey');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeHttpHeader('X-Pagination-Total-Count', 0);
    }

    public function getAdminListDeletedDontShowTest(ApiTester $I)
    {
        $I->haveInDatabase('survey', [
            'id'           => 1,
            'title'        => 'Lorem ipsum.',
            'status'       => -1,
            'category_id'  => 20,
            'start_date'   => (new Carbon())->toDateString(),
            'end_date'     => (new Carbon())->addDays(7)->toDateString(),
            'external_url' => 'http://google.com',
            'created_by'   => 1,
            'updated_by'   => 1,
            'created_at'   => '1554706345',
            'updated_at'   => '1554706345',
        ]);

        $I->amStaff();

        $I->sendGET('/v1/survey');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeHttpHeader('X-Pagination-Total-Count', 0);
    }

    public function getUserListDisabledDontShowTest(ApiTester $I)
    {
        $I->haveInDatabase('survey', [
            'id'           => 1,
            'title'        => 'Lorem ipsum.',
            'status'       => 1,
            'category_id'  => 20,
            'start_date'   => (new Carbon())->toDateString(),
            'end_date'     => (new Carbon())->addDays(7)->toDateString(),
            'external_url' => 'http://google.com',
            'created_by'   => 1,
            'updated_by'   => 1,
            'created_at'   => '1554706345',
            'updated_at'   => '1554706345',
        ]);

        $I->amUser('staffrw');

        $I->sendGET('/v1/survey');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeHttpHeader('X-Pagination-Total-Count', 0);
    }

    public function getAdminListDisabledShowTest(ApiTester $I)
    {
        $I->haveInDatabase('survey', [
            'id'           => 1,
            'title'        => 'Lorem ipsum.',
            'status'       => 1,
            'category_id'  => 20,
            'start_date'   => (new Carbon())->toDateString(),
            'end_date'     => (new Carbon())->addDays(7)->toDateString(),
            'external_url' => 'http://google.com',
            'created_by'   => 1,
            'updated_by'   => 1,
            'created_at'   => '1554706345',
            'updated_at'   => '1554706345',
        ]);

        $I->amStaff();

        $I->sendGET('/v1/survey');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $data = $I->grabDataFromResponseByJsonPath('$.data.items[0]');

        $I->assertEquals(1, $data[0]['id']);
    }

    public function getUserListDraftDontShowTest(ApiTester $I)
    {
        $I->haveInDatabase('survey', [
            'id'           => 1,
            'title'        => 'Lorem ipsum.',
            'status'       => 0,
            'category_id'  => 20,
            'start_date'   => (new Carbon())->toDateString(),
            'end_date'     => (new Carbon())->addDays(7)->toDateString(),
            'external_url' => 'http://google.com',
            'created_by'   => 1,
            'updated_by'   => 1,
            'created_at'   => '1554706345',
            'updated_at'   => '1554706345',
        ]);

        $I->amUser('staffrw');

        $I->sendGET('/v1/survey');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeHttpHeader('X-Pagination-Total-Count', 0);
    }

    public function getAdminListDraftShowTest(ApiTester $I)
    {
        $I->haveInDatabase('survey', [
            'id'           => 1,
            'title'        => 'Lorem ipsum.',
            'status'       => 0,
            'category_id'  => 20,
            'start_date'   => (new Carbon())->toDateString(),
            'end_date'     => (new Carbon())->addDays(7)->toDateString(),
            'external_url' => 'http://google.com',
            'created_by'   => 1,
            'updated_by'   => 1,
            'created_at'   => '1554706345',
            'updated_at'   => '1554706345',
        ]);

        $I->amStaff();

        $I->sendGET('/v1/survey');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $data = $I->grabDataFromResponseByJsonPath('$.data.items[0]');

        $I->assertEquals(1, $data[0]['id']);
    }

    public function getUserListActiveDateTest(ApiTester $I)
    {
        $I->haveInDatabase('survey', [
            'id'           => 1,
            'title'        => 'Lorem ipsum.',
            'status'       => 10,
            'category_id'  => 20,
            'start_date'   => (new Carbon())->toDateString(),
            'end_date'     => (new Carbon())->addDays(7)->toDateString(),
            'external_url' => 'http://google.com',
            'created_by'   => 1,
            'updated_by'   => 1,
            'created_at'   => '1554706345',
            'updated_at'   => '1554706345',
        ]);

        $I->amUser('staffrw');

        $I->sendGET('/v1/survey');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $data = $I->grabDataFromResponseByJsonPath('$.data.items[0]');

        $I->assertEquals(1, $data[0]['id']);
    }

    public function getUserListActiveEndDateTest(ApiTester $I)
    {
        $I->haveInDatabase('survey', [
            'id'           => 1,
            'title'        => 'Lorem ipsum.',
            'status'       => 10,
            'category_id'  => 20,
            'start_date'   => (new Carbon())->subDays(7)->toDateString(),
            'end_date'     => (new Carbon())->toDateString(),
            'external_url' => 'http://google.com',
            'created_by'   => 1,
            'updated_by'   => 1,
            'created_at'   => '1554706345',
            'updated_at'   => '1554706345',
        ]);

        $I->amUser('staffrw');

        $I->sendGET('/v1/survey');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $data = $I->grabDataFromResponseByJsonPath('$.data.items[0]');

        $I->assertEquals(1, $data[0]['id']);
    }

    public function getUserListCannotSeePastDateTest(ApiTester $I)
    {
        $I->haveInDatabase('survey', [
            'id'           => 1,
            'title'        => 'Lorem ipsum.',
            'status'       => 10,
            'category_id'  => 20,
            'start_date'   => (new Carbon())->subDays(7)->toDateString(),
            'end_date'     => (new Carbon())->subDays(1)->toDateString(),
            'external_url' => 'http://google.com',
            'created_by'   => 1,
            'updated_by'   => 1,
            'created_at'   => '1554706345',
            'updated_at'   => '1554706345',
        ]);

        $I->amUser('staffrw');

        $I->sendGET('/v1/survey');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeHttpHeader('X-Pagination-Total-Count', 0);
    }

    public function getUserListCannotSeeFutureDateTest(ApiTester $I)
    {
        $I->haveInDatabase('survey', [
            'id'           => 1,
            'title'        => 'Lorem ipsum.',
            'status'       => 10,
            'category_id'  => 20,
            'start_date'   => (new Carbon())->addDays(7)->toDateString(),
            'end_date'     => (new Carbon())->addDays(14)->toDateString(),
            'external_url' => 'http://google.com',
            'created_by'   => 1,
            'updated_by'   => 1,
            'created_at'   => '1554706345',
            'updated_at'   => '1554706345',
        ]);

        $I->amUser('staffrw');

        $I->sendGET('/v1/survey');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeHttpHeader('X-Pagination-Total-Count', 0);
    }

    public function getAdminListAllDateTest(ApiTester $I)
    {
        $I->haveInDatabase('survey', [
            'id'           => 1,
            'title'        => 'Lorem ipsum.',
            'status'       => 10,
            'category_id'  => 20,
            'start_date'   => (new Carbon())->toDateString(),
            'end_date'     => (new Carbon())->addDays(7)->toDateString(),
            'external_url' => 'http://google.com',
            'created_by'   => 1,
            'updated_by'   => 1,
            'created_at'   => '1554706345',
            'updated_at'   => '1554706345',
        ]);

        $I->haveInDatabase('survey', [
            'id'           => 2,
            'title'        => 'Lorem ipsum.',
            'status'       => 10,
            'category_id'  => 20,
            'start_date'   => (new Carbon())->subDays(7),
            'end_date'     => (new Carbon())->subDays(1)->toDateString(),
            'external_url' => 'http://google.com',
            'created_by'   => 1,
            'updated_by'   => 1,
            'created_at'   => '1554706345',
            'updated_at'   => '1554706345',
        ]);

        $I->haveInDatabase('survey', [
            'id'           => 3,
            'title'        => 'Lorem ipsum.',
            'status'       => 10,
            'category_id'  => 20,
            'start_date'   => (new Carbon())->addDays(7),
            'end_date'     => (new Carbon())->addDays(14)->toDateString(),
            'external_url' => 'http://google.com',
            'created_by'   => 1,
            'updated_by'   => 1,
            'created_at'   => '1554706345',
            'updated_at'   => '1554706345',
        ]);

        $I->amStaff();

        $I->sendGET('/v1/survey');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $data = $I->grabDataFromResponseByJsonPath('$.data.items');

        $I->assertEquals(1, $data[0][0]['id']);
        $I->assertEquals(2, $data[0][1]['id']);
        $I->assertEquals(3, $data[0][2]['id']);
    }

    public function getShowTest(ApiTester $I)
    {
        $I->haveInDatabase('survey', [
            'id'           => 1,
            'title'        => 'Lorem ipsum.',
            'status'       => 0,
            'category_id'  => 20,
            'external_url' => 'http://google.com',
            'created_by'   => 1,
            'updated_by'   => 1,
            'created_at'   => '1554706345',
            'updated_at'   => '1554706345',
        ]);

        $I->amStaff();

        $I->sendGET('/v1/survey/1');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);
    }

    public function postCreateUnauthorizedTest(ApiTester $I)
    {
        $data = [];

        $I->amStaff('staffkec');
        $I->sendPOST('/v1/survey', $data);
        $I->canSeeResponseCodeIs(403);

        $I->amStaff('staffkel');
        $I->sendPOST('/v1/survey', $data);
        $I->canSeeResponseCodeIs(403);

        $I->amUser('staffrw');
        $I->sendPOST('/v1/survey', $data);
        $I->canSeeResponseCodeIs(403);

        $I->amUser('user');
        $I->sendPOST('/v1/survey', $data);
        $I->canSeeResponseCodeIs(403);
    }

    public function postCreateTest(ApiTester $I)
    {
        $I->amStaff();

        $data = [
            'title'        => 'Lorem ipsum',
            'external_url' => 'http://google.com',
            'category_id'  => 20,
            'status'       => 0,
        ];

        $I->sendPOST('/v1/survey', $data);
        $I->canSeeResponseCodeIs(201);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 201,
        ]);

        $I->seeInDatabase('survey', [
            'title'        => 'Lorem ipsum',
            'external_url' => 'http://google.com',
            'category_id'  => 20,
        ]);
    }

    public function postUpdateUnauthorizedTest(ApiTester $I)
    {
        $I->amUser('user');

        $data = [];

        $I->sendPUT('/v1/survey/1', $data);
        $I->canSeeResponseCodeIs(403);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => false,
            'status'  => 403,
        ]);
    }

    public function postUpdateTest(ApiTester $I)
    {
        $I->haveInDatabase('survey', [
            'id'           => 1,
            'title'        => 'Lorem ipsum.',
            'status'       => 0,
            'category_id'  => 20,
            'external_url' => 'http://google.com',
            'created_by'   => 1,
            'updated_by'   => 1,
            'created_at'   => '1554706345',
            'updated_at'   => '1554706345',
        ]);

        $I->amStaff();

        $data = [
            'title'        => 'Lorem ipsum updated',
            'external_url' => 'http://google-updated.com',
            'category_id'  => 21,
            'status'       => 0,
        ];

        $I->sendPUT('/v1/survey/1', $data);
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeInDatabase('survey', [
            'title'        => 'Lorem ipsum updated',
            'external_url' => 'http://google-updated.com',
            'category_id'  => 21,
        ]);
    }

    public function deleteUnauthorizedTest(ApiTester $I)
    {
        $I->amUser('user');

        $I->sendDELETE('/v1/survey/1');
        $I->canSeeResponseCodeIs(403);
    }

    public function deleteTest(ApiTester $I)
    {
        $I->haveInDatabase('survey', [
            'id'           => 1,
            'title'        => 'Lorem ipsum.',
            'status'       => 0,
            'category_id'  => 20,
            'external_url' => 'http://google.com',
            'created_by'   => 1,
            'updated_by'   => 1,
            'created_at'   => '1554706345',
            'updated_at'   => '1554706345',
        ]);

        $I->amStaff();

        $I->sendDELETE('/v1/survey/1');
        $I->canSeeResponseCodeIs(204);

        $I->seeInDatabase('survey', ['id' => 1, 'status' => -1]);
    }
}
