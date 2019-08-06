<?php

use Carbon\Carbon;

class PollingCest
{
    public function _before(ApiTester $I)
    {
        Yii::$app->db->createCommand()->checkIntegrity(false)->execute();

        Yii::$app->db->createCommand('TRUNCATE polling_votes')->execute();
        Yii::$app->db->createCommand('TRUNCATE polling_answers')->execute();
        Yii::$app->db->createCommand('TRUNCATE polling')->execute();
    }

    public function getUserListTest(ApiTester $I)
    {
        $I->amUser('user');

        $I->sendGET('/v1/polling');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);
    }

    public function getUserRwListTest(ApiTester $I)
    {
        $I->amUser('staffrw');

        $I->sendGET('/v1/polling');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);
    }

    public function getUserListPublishedShowTest(ApiTester $I)
    {
        $I->haveInDatabase('polling', [
            'id'          => 1,
            'name'        => 'Lorem ipsum.',
            'question'    => 'Lorem ipsum updated',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'excerpt'     => 'Lorem ipsum dolor sit amet',
            'status'      => 10,
            'kabkota_id'  => 22,
            'kec_id'      => 431,
            'kel_id'      => null,
            'rw'          => null,
            'category_id' => 20,
            'start_date'  => (new Carbon())->toDateString(),
            'end_date'    => (new Carbon())->addDays(7)->toDateString(),
            'created_by'  => 1,
            'updated_by'  => 1,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->amUser('staffrw');

        $I->sendGET('/v1/polling');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $data = $I->grabDataFromResponseByJsonPath('$.data.items[0]');

        $I->assertEquals(1, $data[0]['id']);
    }

    public function getAdminListPublishedShowTest(ApiTester $I)
    {
        $I->haveInDatabase('polling', [
            'id'          => 1,
            'name'        => 'Lorem ipsum.',
            'question'    => 'Lorem ipsum updated',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'excerpt'     => 'Lorem ipsum dolor sit amet',
            'kabkota_id'  => 22,
            'kec_id'      => 431,
            'kel_id'      => null,
            'rw'          => null,
            'status'      => 10,
            'category_id' => 20,
            'start_date'  => (new Carbon())->toDateString(),
            'end_date'    => (new Carbon())->addDays(7)->toDateString(),
            'created_by'  => 1,
            'updated_by'  => 1,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->amStaff();

        $I->sendGET('/v1/polling');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $data = $I->grabDataFromResponseByJsonPath('$.data.items[0]');

        $I->assertEquals(1, $data[0]['id']);
    }

    public function getUserListDeletedDontShowTest(ApiTester $I)
    {
        $I->haveInDatabase('polling', [
            'id'          => 1,
            'name'        => 'Lorem ipsum.',
            'question'    => 'Lorem ipsum updated',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'excerpt'     => 'Lorem ipsum dolor sit amet',
            'status'      => -1,
            'kabkota_id'  => 22,
            'kec_id'      => 431,
            'kel_id'      => null,
            'rw'          => null,
            'category_id' => 20,
            'start_date'  => (new Carbon())->toDateString(),
            'end_date'    => (new Carbon())->addDays(7)->toDateString(),
            'created_by'  => 1,
            'updated_by'  => 1,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->amUser('staffrw');

        $I->sendGET('/v1/polling');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeHttpHeader('X-Pagination-Total-Count', 0);
    }

    public function getAdminListDeletedDontShowTest(ApiTester $I)
    {
        $I->haveInDatabase('polling', [
            'id'          => 1,
            'name'        => 'Lorem ipsum.',
            'question'    => 'Lorem ipsum updated',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'excerpt'     => 'Lorem ipsum dolor sit amet',
            'status'      => -1,
            'kabkota_id'  => 22,
            'kec_id'      => 431,
            'kel_id'      => null,
            'rw'          => null,
            'category_id' => 20,
            'start_date'  => (new Carbon())->toDateString(),
            'end_date'    => (new Carbon())->addDays(7)->toDateString(),
            'created_by'  => 1,
            'updated_by'  => 1,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->amStaff();

        $I->sendGET('/v1/polling');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeHttpHeader('X-Pagination-Total-Count', 0);
    }

    public function getUserListDisabledDontShowTest(ApiTester $I)
    {
        $I->haveInDatabase('polling', [
            'id'          => 1,
            'name'        => 'Lorem ipsum.',
            'question'    => 'Lorem ipsum updated',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'excerpt'     => 'Lorem ipsum dolor sit amet',
            'status'      => 1,
            'kabkota_id'  => 22,
            'kec_id'      => 431,
            'kel_id'      => null,
            'rw'          => null,
            'category_id' => 20,
            'start_date'  => (new Carbon())->toDateString(),
            'end_date'    => (new Carbon())->addDays(7)->toDateString(),
            'created_by'  => 1,
            'updated_by'  => 1,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->amUser('staffrw');

        $I->sendGET('/v1/polling');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeHttpHeader('X-Pagination-Total-Count', 0);
    }

    public function getAdminListDisabledShowTest(ApiTester $I)
    {
        $I->haveInDatabase('polling', [
            'id'          => 1,
            'name'        => 'Lorem ipsum.',
            'question'    => 'Lorem ipsum updated',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'excerpt'     => 'Lorem ipsum dolor sit amet',
            'status'      => 1,
            'kabkota_id'  => 22,
            'kec_id'      => 431,
            'kel_id'      => null,
            'rw'          => null,
            'category_id' => 20,
            'start_date'  => (new Carbon())->toDateString(),
            'end_date'    => (new Carbon())->addDays(7)->toDateString(),
            'created_by'  => 1,
            'updated_by'  => 1,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->amStaff();

        $I->sendGET('/v1/polling');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $data = $I->grabDataFromResponseByJsonPath('$.data.items[0]');

        $I->assertEquals(1, $data[0]['id']);
    }

    public function getUserListDraftDontShowTest(ApiTester $I)
    {
        $I->haveInDatabase('polling', [
            'id'          => 1,
            'name'        => 'Lorem ipsum.',
            'question'    => 'Lorem ipsum updated',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'excerpt'     => 'Lorem ipsum dolor sit amet',
            'status'      => 0,
            'kabkota_id'  => 22,
            'kec_id'      => 431,
            'kel_id'      => null,
            'rw'          => null,
            'category_id' => 20,
            'start_date'  => (new Carbon())->toDateString(),
            'end_date'    => (new Carbon())->addDays(7)->toDateString(),
            'created_by'  => 1,
            'updated_by'  => 1,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->amUser('staffrw');

        $I->sendGET('/v1/polling');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeHttpHeader('X-Pagination-Total-Count', 0);
    }

    public function getAdminListOwnDraftShowTest(ApiTester $I)
    {
        $I->haveInDatabase('polling', [
            'id'          => 2,
            'name'        => 'Lorem ipsum.',
            'question'    => 'Lorem ipsum updated',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'excerpt'     => 'Lorem ipsum dolor sit amet',
            'status'      => 0,
            'kabkota_id'  => 22,
            'kec_id'      => 431,
            'kel_id'      => null,
            'rw'          => null,
            'category_id' => 20,
            'start_date'  => (new Carbon())->toDateString(),
            'end_date'    => (new Carbon())->addDays(7)->toDateString(),
            'created_by'  => 2,
            'updated_by'  => 2,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->haveInDatabase('polling', [
            'id'          => 1,
            'name'        => 'Lorem ipsum.',
            'question'    => 'Lorem ipsum updated',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'excerpt'     => 'Lorem ipsum dolor sit amet',
            'status'      => 0,
            'kabkota_id'  => 22,
            'kec_id'      => 431,
            'kel_id'      => null,
            'rw'          => null,
            'category_id' => 20,
            'start_date'  => (new Carbon())->toDateString(),
            'end_date'    => (new Carbon())->addDays(7)->toDateString(),
            'created_by'  => 1,
            'updated_by'  => 1,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->amStaff();

        $I->sendGET('/v1/polling');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $data = $I->grabDataFromResponseByJsonPath('$.data.items[0]');

        $I->assertEquals(1, $data[0]['id']);
    }

    public function getUserListActiveDateTest(ApiTester $I)
    {
        $I->haveInDatabase('polling', [
            'id'          => 1,
            'name'        => 'Lorem ipsum.',
            'question'    => 'Lorem ipsum updated',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'excerpt'     => 'Lorem ipsum dolor sit amet',
            'status'      => 10,
            'kabkota_id'  => 22,
            'kec_id'      => 431,
            'kel_id'      => null,
            'rw'          => null,
            'category_id' => 20,
            'start_date'  => (new Carbon())->toDateString(),
            'end_date'    => (new Carbon())->addDays(7)->toDateString(),
            'created_by'  => 1,
            'updated_by'  => 1,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->amUser('staffrw');

        $I->sendGET('/v1/polling');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $data = $I->grabDataFromResponseByJsonPath('$.data.items[0]');

        $I->assertEquals(1, $data[0]['id']);
    }

    public function getUserListActiveEndDateTest(ApiTester $I)
    {
        $I->haveInDatabase('polling', [
            'id'          => 1,
            'name'        => 'Lorem ipsum.',
            'question'    => 'Lorem ipsum updated',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'excerpt'     => 'Lorem ipsum dolor sit amet',
            'status'      => 10,
            'kabkota_id'  => 22,
            'kec_id'      => 431,
            'kel_id'      => null,
            'rw'          => null,
            'category_id' => 20,
            'start_date'  => (new Carbon())->subDays(7)->toDateString(),
            'end_date'    => (new Carbon())->toDateString(),
            'created_by'  => 1,
            'updated_by'  => 1,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->amUser('staffrw');

        $I->sendGET('/v1/polling');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $data = $I->grabDataFromResponseByJsonPath('$.data.items[0]');

        $I->assertEquals(1, $data[0]['id']);
    }

    public function getUserListCannotSeePastDateTest(ApiTester $I)
    {
        $I->haveInDatabase('polling', [
            'id'          => 1,
            'name'        => 'Lorem ipsum.',
            'question'    => 'Lorem ipsum updated',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'excerpt'     => 'Lorem ipsum dolor sit amet',
            'status'      => 10,
            'kabkota_id'  => 22,
            'kec_id'      => 431,
            'kel_id'      => null,
            'rw'          => null,
            'category_id' => 20,
            'start_date'  => (new Carbon())->subDays(7)->toDateString(),
            'end_date'    => (new Carbon())->subDays(1)->toDateString(),
            'created_by'  => 1,
            'updated_by'  => 1,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->amUser('staffrw');

        $I->sendGET('/v1/polling');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeHttpHeader('X-Pagination-Total-Count', 0);
    }

    public function getUserListCannotSeeFutureDateTest(ApiTester $I)
    {
        $I->haveInDatabase('polling', [
            'id'          => 1,
            'name'        => 'Lorem ipsum.',
            'question'    => 'Lorem ipsum updated',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'excerpt'     => 'Lorem ipsum dolor sit amet',
            'status'      => 10,
            'kabkota_id'  => 22,
            'kec_id'      => 431,
            'kel_id'      => null,
            'rw'          => null,
            'category_id' => 20,
            'start_date'  => (new Carbon())->addDays(7)->toDateString(),
            'end_date'    => (new Carbon())->addDays(14)->toDateString(),
            'created_by'  => 1,
            'updated_by'  => 1,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->amUser('staffrw');

        $I->sendGET('/v1/polling');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeHttpHeader('X-Pagination-Total-Count', 0);
    }

    public function getAdminListAllDateTest(ApiTester $I)
    {
        $I->haveInDatabase('polling', [
            'id'          => 1,
            'name'        => 'Lorem ipsum.',
            'question'    => 'Lorem ipsum updated',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'excerpt'     => 'Lorem ipsum dolor sit amet',
            'status'      => 10,
            'kabkota_id'  => 22,
            'kec_id'      => 431,
            'kel_id'      => null,
            'rw'          => null,
            'category_id' => 20,
            'start_date'  => (new Carbon())->toDateString(),
            'end_date'    => (new Carbon())->addDays(7)->toDateString(),
            'created_by'  => 1,
            'updated_by'  => 1,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->haveInDatabase('polling', [
            'id'          => 2,
            'name'        => 'Lorem ipsum.',
            'question'    => 'Lorem ipsum updated',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'excerpt'     => 'Lorem ipsum dolor sit amet',
            'status'      => 10,
            'kabkota_id'  => 22,
            'kec_id'      => 431,
            'kel_id'      => null,
            'rw'          => null,
            'category_id' => 20,
            'start_date'  => (new Carbon())->subDays(7),
            'end_date'    => (new Carbon())->subDays(1)->toDateString(),
            'created_by'  => 1,
            'updated_by'  => 1,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->haveInDatabase('polling', [
            'id'          => 3,
            'name'        => 'Lorem ipsum.',
            'question'    => 'Lorem ipsum updated',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'excerpt'     => 'Lorem ipsum dolor sit amet',
            'status'      => 10,
            'kabkota_id'  => 22,
            'kec_id'      => 431,
            'kel_id'      => null,
            'rw'          => null,
            'category_id' => 20,
            'start_date'  => (new Carbon())->addDays(7),
            'end_date'    => (new Carbon())->addDays(14)->toDateString(),
            'created_by'  => 1,
            'updated_by'  => 1,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->amStaff();

        $I->sendGET('/v1/polling');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $data = $I->grabDataFromResponseByJsonPath('$.data.items');

        $I->assertEquals(1, $data[0][0]['id']);
        $I->assertEquals(2, $data[0][1]['id']);
        $I->assertEquals(3, $data[0][2]['id']);
    }

    public function getAdminKabKotaListTest(ApiTester $I)
    {
        $I->haveInDatabase('polling', [
            'id'          => 1,
            'name'        => 'Lorem ipsum.',
            'question'    => 'Lorem ipsum updated',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'excerpt'     => 'Lorem ipsum dolor sit amet',
            'status'      => 10,
            'kabkota_id'  => null,
            'kec_id'      => null,
            'kel_id'      => null,
            'rw'          => null,
            'category_id' => 20,
            'start_date'  => (new Carbon())->addDays(7),
            'end_date'    => (new Carbon())->addDays(14)->toDateString(),
            'created_by'  => 1,
            'updated_by'  => 1,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->haveInDatabase('polling', [
            'id'          => 2,
            'name'        => 'Lorem ipsum.',
            'question'    => 'Lorem ipsum updated',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'excerpt'     => 'Lorem ipsum dolor sit amet',
            'status'      => 10,
            'kabkota_id'  => 23,
            'kec_id'      => null,
            'kel_id'      => null,
            'rw'          => null,
            'category_id' => 20,
            'start_date'  => (new Carbon())->subDays(7),
            'end_date'    => (new Carbon())->subDays(1)->toDateString(),
            'created_by'  => 1,
            'updated_by'  => 1,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->haveInDatabase('polling', [
            'id'          => 3,
            'name'        => 'Lorem ipsum.',
            'question'    => 'Lorem ipsum updated',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'excerpt'     => 'Lorem ipsum dolor sit amet',
            'status'      => 10,
            'kabkota_id'  => 22,
            'kec_id'      => null,
            'kel_id'      => null,
            'rw'          => null,
            'category_id' => 20,
            'start_date'  => (new Carbon())->toDateString(),
            'end_date'    => (new Carbon())->addDays(7)->toDateString(),
            'created_by'  => 1,
            'updated_by'  => 1,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->amStaff('staffkabkota');

        $I->sendGET('/v1/polling');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $data = $I->grabDataFromResponseByJsonPath('$.data.items');

        $I->assertEquals(null, $data[0][0]['kabkota_id']);
        $I->assertEquals(null, $data[0][0]['kec_id']);
        $I->assertEquals(null, $data[0][0]['kel_id']);

        $I->assertEquals(22, $data[0][1]['kabkota_id']);
        $I->assertEquals(null, $data[0][1]['kec_id']);
        $I->assertEquals(null, $data[0][1]['kel_id']);
    }

    public function getAdminKecListTest(ApiTester $I)
    {
        $I->haveInDatabase('polling', [
            'id'          => 1,
            'name'        => 'Lorem ipsum.',
            'question'    => 'Lorem ipsum updated',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'excerpt'     => 'Lorem ipsum dolor sit amet',
            'status'      => 10,
            'kabkota_id'  => null,
            'kec_id'      => null,
            'kel_id'      => null,
            'rw'          => null,
            'category_id' => 20,
            'start_date'  => (new Carbon())->addDays(7),
            'end_date'    => (new Carbon())->addDays(14)->toDateString(),
            'created_by'  => 1,
            'updated_by'  => 1,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->haveInDatabase('polling', [
            'id'          => 2,
            'name'        => 'Lorem ipsum.',
            'question'    => 'Lorem ipsum updated',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'excerpt'     => 'Lorem ipsum dolor sit amet',
            'status'      => 10,
            'kabkota_id'  => 23,
            'kec_id'      => null,
            'kel_id'      => null,
            'rw'          => null,
            'category_id' => 20,
            'start_date'  => (new Carbon())->subDays(7),
            'end_date'    => (new Carbon())->subDays(1)->toDateString(),
            'created_by'  => 1,
            'updated_by'  => 1,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->haveInDatabase('polling', [
            'id'          => 3,
            'name'        => 'Lorem ipsum.',
            'question'    => 'Lorem ipsum updated',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'excerpt'     => 'Lorem ipsum dolor sit amet',
            'status'      => 10,
            'kabkota_id'  => 22,
            'kec_id'      => null,
            'kel_id'      => null,
            'rw'          => null,
            'category_id' => 20,
            'start_date'  => (new Carbon())->toDateString(),
            'end_date'    => (new Carbon())->addDays(7)->toDateString(),
            'created_by'  => 1,
            'updated_by'  => 1,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->haveInDatabase('polling', [
            'id'          => 4,
            'name'        => 'Lorem ipsum.',
            'question'    => 'Lorem ipsum updated',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'excerpt'     => 'Lorem ipsum dolor sit amet',
            'status'      => 10,
            'kabkota_id'  => 22,
            'kec_id'      => 432,
            'kel_id'      => null,
            'rw'          => null,
            'category_id' => 20,
            'start_date'  => (new Carbon())->toDateString(),
            'end_date'    => (new Carbon())->addDays(7)->toDateString(),
            'created_by'  => 1,
            'updated_by'  => 1,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->haveInDatabase('polling', [
            'id'          => 5,
            'name'        => 'Lorem ipsum.',
            'question'    => 'Lorem ipsum updated',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'excerpt'     => 'Lorem ipsum dolor sit amet',
            'status'      => 10,
            'kabkota_id'  => 22,
            'kec_id'      => 431,
            'kel_id'      => null,
            'rw'          => null,
            'category_id' => 20,
            'start_date'  => (new Carbon())->toDateString(),
            'end_date'    => (new Carbon())->addDays(7)->toDateString(),
            'created_by'  => 1,
            'updated_by'  => 1,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->amStaff('staffkec');

        $I->sendGET('/v1/polling');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $data = $I->grabDataFromResponseByJsonPath('$.data.items');

        $I->assertEquals(null, $data[0][0]['kabkota_id']);
        $I->assertEquals(null, $data[0][0]['kec_id']);
        $I->assertEquals(null, $data[0][0]['kel_id']);

        $I->assertEquals(22, $data[0][1]['kabkota_id']);
        $I->assertEquals(null, $data[0][1]['kec_id']);
        $I->assertEquals(null, $data[0][1]['kel_id']);

        $I->assertEquals(22, $data[0][2]['kabkota_id']);
        $I->assertEquals(431, $data[0][2]['kec_id']);
        $I->assertEquals(null, $data[0][2]['kel_id']);
    }

    public function getAdminKelListTest(ApiTester $I)
    {
        $I->haveInDatabase('polling', [
            'id'          => 1,
            'name'        => 'Lorem ipsum.',
            'question'    => 'Lorem ipsum updated',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'excerpt'     => 'Lorem ipsum dolor sit amet',
            'status'      => 10,
            'kabkota_id'  => null,
            'kec_id'      => null,
            'kel_id'      => null,
            'rw'          => null,
            'category_id' => 20,
            'start_date'  => (new Carbon())->addDays(7),
            'end_date'    => (new Carbon())->addDays(14)->toDateString(),
            'created_by'  => 1,
            'updated_by'  => 1,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->haveInDatabase('polling', [
            'id'          => 2,
            'name'        => 'Lorem ipsum.',
            'question'    => 'Lorem ipsum updated',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'excerpt'     => 'Lorem ipsum dolor sit amet',
            'status'      => 10,
            'kabkota_id'  => 23,
            'kec_id'      => null,
            'kel_id'      => null,
            'rw'          => null,
            'category_id' => 20,
            'start_date'  => (new Carbon())->subDays(7),
            'end_date'    => (new Carbon())->subDays(1)->toDateString(),
            'created_by'  => 1,
            'updated_by'  => 1,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->haveInDatabase('polling', [
            'id'          => 3,
            'name'        => 'Lorem ipsum.',
            'question'    => 'Lorem ipsum updated',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'excerpt'     => 'Lorem ipsum dolor sit amet',
            'status'      => 10,
            'kabkota_id'  => 22,
            'kec_id'      => null,
            'kel_id'      => null,
            'rw'          => null,
            'category_id' => 20,
            'start_date'  => (new Carbon())->toDateString(),
            'end_date'    => (new Carbon())->addDays(7)->toDateString(),
            'created_by'  => 1,
            'updated_by'  => 1,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->haveInDatabase('polling', [
            'id'          => 4,
            'name'        => 'Lorem ipsum.',
            'question'    => 'Lorem ipsum updated',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'excerpt'     => 'Lorem ipsum dolor sit amet',
            'status'      => 10,
            'kabkota_id'  => 22,
            'kec_id'      => 431,
            'kel_id'      => null,
            'rw'          => null,
            'category_id' => 20,
            'start_date'  => (new Carbon())->toDateString(),
            'end_date'    => (new Carbon())->addDays(7)->toDateString(),
            'created_by'  => 1,
            'updated_by'  => 1,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->haveInDatabase('polling', [
            'id'          => 5,
            'name'        => 'Lorem ipsum.',
            'question'    => 'Lorem ipsum updated',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'excerpt'     => 'Lorem ipsum dolor sit amet',
            'status'      => 10,
            'kabkota_id'  => 22,
            'kec_id'      => 431,
            'kel_id'      => 6094,
            'rw'          => null,
            'category_id' => 20,
            'start_date'  => (new Carbon())->toDateString(),
            'end_date'    => (new Carbon())->addDays(7)->toDateString(),
            'created_by'  => 1,
            'updated_by'  => 1,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->haveInDatabase('polling', [
            'id'          => 6,
            'name'        => 'Lorem ipsum.',
            'question'    => 'Lorem ipsum updated',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'excerpt'     => 'Lorem ipsum dolor sit amet',
            'status'      => 10,
            'kabkota_id'  => 22,
            'kec_id'      => 431,
            'kel_id'      => 6093,
            'rw'          => null,
            'category_id' => 20,
            'start_date'  => (new Carbon())->toDateString(),
            'end_date'    => (new Carbon())->addDays(7)->toDateString(),
            'created_by'  => 1,
            'updated_by'  => 1,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->amStaff('staffkel');

        $I->sendGET('/v1/polling');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $data = $I->grabDataFromResponseByJsonPath('$.data.items');

        $I->assertEquals(null, $data[0][0]['kabkota_id']);
        $I->assertEquals(null, $data[0][0]['kec_id']);
        $I->assertEquals(null, $data[0][0]['kel_id']);

        $I->assertEquals(22, $data[0][1]['kabkota_id']);
        $I->assertEquals(null, $data[0][1]['kec_id']);
        $I->assertEquals(null, $data[0][1]['kel_id']);

        $I->assertEquals(22, $data[0][2]['kabkota_id']);
        $I->assertEquals(431, $data[0][2]['kec_id']);
        $I->assertEquals(null, $data[0][2]['kel_id']);

        $I->assertEquals(22, $data[0][3]['kabkota_id']);
        $I->assertEquals(431, $data[0][3]['kec_id']);
        $I->assertEquals(6093, $data[0][3]['kel_id']);
    }

    public function getListByUserAreaKabkota(ApiTester $I)
    {
        $I->haveInDatabase('polling', [
            'id'          => 1,
            'name'        => 'Lorem ipsum.',
            'question'    => 'Lorem ipsum updated',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'excerpt'     => 'Lorem ipsum dolor sit amet',
            'kabkota_id'  => 22,
            'kec_id'      => null,
            'kel_id'      => null,
            'rw'          => null,
            'status'      => 10,
            'category_id' => 20,
            'start_date'  => (new Carbon())->toDateString(),
            'end_date'    => (new Carbon())->addDays(7)->toDateString(),
            'created_by'  => 1,
            'updated_by'  => 1,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->amUser('user');

        $I->sendGET('/v1/polling');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $data = $I->grabDataFromResponseByJsonPath('$.data.items');

        $I->assertEquals(1, $data[0][0]['id']);
    }

    public function getListByUserCannotSeeOtherAreaKabkota(ApiTester $I)
    {
        $I->haveInDatabase('polling', [
            'id'          => 1,
            'name'        => 'Lorem ipsum.',
            'question'    => 'Lorem ipsum updated',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'excerpt'     => 'Lorem ipsum dolor sit amet',
            'kabkota_id'  => 21,
            'kec_id'      => null,
            'kel_id'      => null,
            'rw'          => null,
            'status'      => 10,
            'category_id' => 20,
            'start_date'  => (new Carbon())->toDateString(),
            'end_date'    => (new Carbon())->addDays(7)->toDateString(),
            'created_by'  => 1,
            'updated_by'  => 1,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->amUser('user');

        $I->sendGET('/v1/polling');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeHttpHeader('X-Pagination-Total-Count', 0);
    }

    public function getListByUserAreaKecamatan(ApiTester $I)
    {
        $I->haveInDatabase('polling', [
            'id'          => 1,
            'name'        => 'Lorem ipsum.',
            'question'    => 'Lorem ipsum updated',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'excerpt'     => 'Lorem ipsum dolor sit amet',
            'kabkota_id'  => 22,
            'kec_id'      => 446,
            'kel_id'      => null,
            'rw'          => null,
            'status'      => 10,
            'category_id' => 20,
            'start_date'  => (new Carbon())->toDateString(),
            'end_date'    => (new Carbon())->addDays(7)->toDateString(),
            'created_by'  => 1,
            'updated_by'  => 1,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->amUser('user');

        $I->sendGET('/v1/polling');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $data = $I->grabDataFromResponseByJsonPath('$.data.items');

        $I->assertEquals(1, $data[0][0]['id']);
    }

    public function getListByUserCannotSeeOtherAreaKecamatan(ApiTester $I)
    {
        $I->haveInDatabase('polling', [
            'id'          => 1,
            'name'        => 'Lorem ipsum.',
            'question'    => 'Lorem ipsum updated',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'excerpt'     => 'Lorem ipsum dolor sit amet',
            'kabkota_id'  => 22,
            'kec_id'      => 447,
            'kel_id'      => null,
            'rw'          => null,
            'status'      => 10,
            'category_id' => 20,
            'start_date'  => (new Carbon())->toDateString(),
            'end_date'    => (new Carbon())->addDays(7)->toDateString(),
            'created_by'  => 1,
            'updated_by'  => 1,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->amUser('user');

        $I->sendGET('/v1/polling');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeHttpHeader('X-Pagination-Total-Count', 0);
    }

    public function getListByUserAreaKelurahan(ApiTester $I)
    {
        $I->haveInDatabase('polling', [
            'id'          => 1,
            'name'        => 'Lorem ipsum.',
            'question'    => 'Lorem ipsum updated',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'excerpt'     => 'Lorem ipsum dolor sit amet',
            'kabkota_id'  => 22,
            'kec_id'      => 446,
            'kel_id'      => 6178,
            'rw'          => null,
            'status'      => 10,
            'category_id' => 20,
            'start_date'  => (new Carbon())->toDateString(),
            'end_date'    => (new Carbon())->addDays(7)->toDateString(),
            'created_by'  => 1,
            'updated_by'  => 1,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->amUser('user');

        $I->sendGET('/v1/polling');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $data = $I->grabDataFromResponseByJsonPath('$.data.items');

        $I->assertEquals(1, $data[0][0]['id']);
    }

    public function getListByUserCannotSeeOtherAreaKelurahan(ApiTester $I)
    {
        $I->haveInDatabase('polling', [
            'id'          => 1,
            'name'        => 'Lorem ipsum.',
            'question'    => 'Lorem ipsum updated',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'excerpt'     => 'Lorem ipsum dolor sit amet',
            'kabkota_id'  => 22,
            'kec_id'      => 446,
            'kel_id'      => 6179,
            'rw'          => null,
            'status'      => 10,
            'category_id' => 20,
            'start_date'  => (new Carbon())->toDateString(),
            'end_date'    => (new Carbon())->addDays(7)->toDateString(),
            'created_by'  => 1,
            'updated_by'  => 1,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->amUser('user');

        $I->sendGET('/v1/polling');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeHttpHeader('X-Pagination-Total-Count', 0);
    }

    public function getListByUserAreaRw(ApiTester $I)
    {
        $I->haveInDatabase('polling', [
            'id'          => 1,
            'name'        => 'Lorem ipsum.',
            'question'    => 'Lorem ipsum updated',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'excerpt'     => 'Lorem ipsum dolor sit amet',
            'kabkota_id'  => 22,
            'kec_id'      => 431,
            'kel_id'      => 6093,
            'rw'          => '001',
            'status'      => 10,
            'category_id' => 20,
            'start_date'  => (new Carbon())->toDateString(),
            'end_date'    => (new Carbon())->addDays(7)->toDateString(),
            'created_by'  => 1,
            'updated_by'  => 1,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->amUser('staffrw');

        $I->sendGET('/v1/polling');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $data = $I->grabDataFromResponseByJsonPath('$.data.items');

        $I->assertEquals(1, $data[0][0]['id']);
    }

    public function getListByUserCannotSeeOtherAreaRw(ApiTester $I)
    {
        $I->haveInDatabase('polling', [
            'id'          => 1,
            'name'        => 'Lorem ipsum.',
            'question'    => 'Lorem ipsum updated',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'excerpt'     => 'Lorem ipsum dolor sit amet',
            'kabkota_id'  => 22,
            'kec_id'      => 431,
            'kel_id'      => 6093,
            'rw'          => '002',
            'status'      => 10,
            'category_id' => 20,
            'start_date'  => (new Carbon())->toDateString(),
            'end_date'    => (new Carbon())->addDays(7)->toDateString(),
            'created_by'  => 1,
            'updated_by'  => 1,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->amUser('user');

        $I->sendGET('/v1/polling');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeHttpHeader('X-Pagination-Total-Count', 0);
    }
}
