<?php

use Carbon\Carbon;

class VideoCest
{
    public function _before(ApiTester $I)
    {
        Yii::$app->db->createCommand()->checkIntegrity(false)->execute();

        Yii::$app->db->createCommand('TRUNCATE likes')->execute();
        Yii::$app->db->createCommand('TRUNCATE videos')->execute();
    }

    public function postCreateUnauthorizedTest(ApiTester $I)
    {
        $I->amUser('user');

        $data = [];

        $I->sendPOST('/v1/videos', $data);
        $I->canSeeResponseCodeIs(403);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => false,
            'status' => 403,
        ]);
    }

    public function postCreateUnauthorizedStaffTest(ApiTester $I)
    {
        $I->amStaff('staffkec');

        $data = [];

        $I->sendPOST('/v1/videos', $data);
        $I->canSeeResponseCodeIs(403);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => false,
            'status' => 403,
        ]);
    }

    public function postAdminCreateTest(ApiTester $I)
    {
        $I->amStaff('admin');

        $data = [
            'id' => 1,
            'title' => 'Lorem ipsum',
            'category_id' => 22,
            'source' => 'youtube',
            'video_url' => 'https://www.youtube.com/watch?v=lorem',
            'kabkota_id' => null,
            'seq' => null,
            'status' => 10
        ];

        $I->sendPOST('/v1/videos', $data);
        $I->canSeeResponseCodeIs(201);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 201,
        ]);

        $data = $I->grabDataFromResponseByJsonPath('$.data');

        $I->assertEquals('Lorem ipsum', $data[0]['title']);
        $I->assertEquals('https://www.youtube.com/watch?v=lorem', $data[0]['video_url']);
        $I->assertEquals(22, $data[0]['category_id']);
        $I->assertEquals(10, $data[0]['status']);
    }

    public function postStaffKabKotaCreateTest(ApiTester $I)
    {
        $I->amStaff('staffkabkota');

        $data = [
            'id' => 1,
            'title' => 'Lorem ipsum',
            'category_id' => 22,
            'source' => 'youtube',
            'video_url' => 'https://www.youtube.com/watch?v=lorem',
            'kabkota_id' => 22,
            'seq' => null,
            'status' => 10
        ];

        $I->sendPOST('/v1/videos', $data);
        $I->canSeeResponseCodeIs(201);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 201,
        ]);

        $data = $I->grabDataFromResponseByJsonPath('$.data');

        $I->assertEquals('Lorem ipsum', $data[0]['title']);
        $I->assertEquals('https://www.youtube.com/watch?v=lorem', $data[0]['video_url']);
        $I->assertEquals(22, $data[0]['category_id']);
        $I->assertEquals(10, $data[0]['status']);
    }

    public function postUpdateUnauthorizedTest(ApiTester $I)
    {
        $I->amUser('user');

        $data = [];

        $I->sendPUT('/v1/videos/1', $data);
        $I->canSeeResponseCodeIs(403);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => false,
            'status' => 403,
        ]);
    }

    public function postAdminUpdateTest(ApiTester $I)
    {
        $I->haveInDatabase('videos', [
            'id' => 1,
            'category_id' => 22,
            'title' => 'Lorem ipsum.',
            'source' => 'youtube',
            'video_url' => 'https://www.youtube.com/watch?v=YvG6D0qJflk',
            'kabkota_id' => null,
            'total_likes' => 0,
            'seq' => null,
            'status' => 10,
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => 1557803314,
            'updated_at' => 1557803314,
        ]);

        $I->amStaff('admin');

        $data = [
            'title' => 'Lorem ipsum updated',
            'video_url' => 'https://www.youtube.com/watch?v=update',
            'category_id' => 23,
            'seq' => 2,
            'status' => 0,
        ];

        $I->sendPUT('/v1/videos/1', $data);
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status' => 200,
        ]);

        $I->seeInDatabase('videos', [
            'title' => 'Lorem ipsum updated',
            'video_url' => 'https://www.youtube.com/watch?v=update',
            'category_id' => 23,
        ]);
    }

    public function postUpdateNotOwnVideoFailTest(ApiTester $I)
    {
        $I->haveInDatabase('videos', [
            'id' => 1,
            'category_id' => 22,
            'title' => 'Lorem ipsum.',
            'source' => 'youtube',
            'video_url' => 'https://www.youtube.com/watch?v=YvG6D0qJflk',
            'kabkota_id' => null,
            'total_likes' => 0,
            'seq' => null,
            'status' => 10,
            'created_by' => 2,
            'updated_by' => 2,
            'created_at' => 1557803314,
            'updated_at' => 1557803314,
        ]);

        $I->amStaff('staffkabkota');

        $data = [
            'title' => 'Lorem ipsum updated',
            'video_url' => 'https://www.youtube.com/watch?v=update',
            'category_id' => 23,
            'seq' => 2,
            'status' => 0,
        ];

        $I->sendPUT('/v1/videos/1', $data);
        $I->canSeeResponseCodeIs(403);
        $I->seeResponseIsJson();
    }

    public function deleteUserUnauthorizedTest(ApiTester $I)
    {
        $I->amUser('user');

        $I->sendDELETE('/v1/videos/1');
        $I->canSeeResponseCodeIs(403);
    }

    public function deleteUserRWUnauthorizedTest(ApiTester $I)
    {
        $I->amUser('staffrw');

        $I->sendDELETE('/v1/videos/1');
        $I->canSeeResponseCodeIs(403);
    }

    public function deleteTest(ApiTester $I)
    {
        $I->haveInDatabase('videos', [
            'id' => 1,
            'category_id' => 22,
            'title' => 'Lorem ipsum.',
            'source' => 'youtube',
            'video_url' => 'https://www.youtube.com/watch?v=YvG6D0qJflk',
            'kabkota_id' => null,
            'total_likes' => 0,
            'seq' => null,
            'status' => 10,
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => 1557803314,
            'updated_at' => 1557803314,
        ]);

        $I->amStaff();

        $I->sendDELETE('/v1/videos/1');
        $I->canSeeResponseCodeIs(204);

        $I->seeInDatabase('videos', ['id' => 1, 'status' => -1]);
    }

    public function deleteNotOwnVideoFailTest(ApiTester $I)
    {
        $I->haveInDatabase('videos', [
            'id' => 1,
            'category_id' => 22,
            'title' => 'Lorem ipsum.',
            'source' => 'youtube',
            'video_url' => 'https://www.youtube.com/watch?v=YvG6D0qJflk',
            'kabkota_id' => null,
            'total_likes' => 0,
            'seq' => null,
            'status' => 10,
            'created_by' => 2,
            'updated_by' => 2,
            'created_at' => 1557803314,
            'updated_at' => 1557803314,
        ]);

        $I->amStaff('staffkabkota');

        $I->sendDELETE('/v1/videos/1');
        $I->canSeeResponseCodeIs(403);
    }

    public function getUserCategoryVideoStatisticsUnauthorizedTest(ApiTester $I)
    {
        $I->amUser('staffrw');

        $I->sendGET('/v1/videos/statistics');
        $I->canSeeResponseCodeIs(403);
    }

    public function getAdminCategoryVideoStatisticsTest(ApiTester $I)
    {
        $I->haveInDatabase('videos', [
            'id' => 1,
            'category_id' => 25,
            'title' => 'Lorem ipsum.',
            'source' => 'youtube',
            'video_url' => 'https://www.youtube.com/watch?v=YvG6D0qJflk',
            'kabkota_id' => null,
            'total_likes' => 0,
            'seq' => null,
            'status' => 10,
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => 1557803314,
            'updated_at' => 1557803314,
        ]);

        $I->amStaff('admin');

        $I->sendGET('/v1/videos/statistics');
        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $data = $I->grabDataFromResponseByJsonPath('$.data.items');

        $I->assertEquals(1, $data[0][0]['count']);
        $I->assertEquals(0, $data[0][1]['count']);
        $I->assertEquals(0, $data[0][2]['count']);
        $I->assertEquals(0, $data[0][3]['count']);
        $I->assertEquals(0, $data[0][4]['count']);
    }
}
