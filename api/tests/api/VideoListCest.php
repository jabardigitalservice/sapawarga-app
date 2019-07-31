<?php

use Carbon\Carbon;

class VideoListCest
{
    public function _before(ApiTester $I)
    {
        Yii::$app->db->createCommand()->checkIntegrity(false)->execute();

        Yii::$app->db->createCommand('TRUNCATE likes')->execute();
        Yii::$app->db->createCommand('TRUNCATE videos')->execute();
    }

    public function getUserListTest(ApiTester $I)
    {
        $I->amUser('user');

        $I->sendGET('/v1/videos');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);
    }

    public function getUserRWListTest(ApiTester $I)
    {
        $I->amUser('staffrw');

        $I->sendGET('/v1/videos');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);
    }

    public function getUserKabKotaListTest(ApiTester $I)
    {
        $I->amStaff('staffkabkota');

        $I->sendGET('/v1/videos');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);
    }

    public function getUserKecListTest(ApiTester $I)
    {
        $I->amStaff('staffkec');

        $I->sendGET('/v1/videos');
        $I->canSeeResponseCodeIs(403);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => false,
            'status' => 403,
        ]);
    }

    public function getUserKelListTest(ApiTester $I)
    {
        $I->amStaff('staffkel');

        $I->sendGET('/v1/videos');
        $I->canSeeResponseCodeIs(403);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => false,
            'status' => 403,
        ]);
    }

    public function getUserListPublishedShowTest(ApiTester $I)
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

        $I->amUser('user');

        $I->sendGET('/v1/videos');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $data = $I->grabDataFromResponseByJsonPath('$.data.items[0]');

        $I->assertEquals(1, $data[0]['id']);
    }

    public function getUserRWListPublishedShowTest(ApiTester $I)
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

        $I->amUser('staffrw');

        $I->sendGET('/v1/videos');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $data = $I->grabDataFromResponseByJsonPath('$.data.items[0]');

        $I->assertEquals(1, $data[0]['id']);
    }

    public function getAdminListPublishedShowTest(ApiTester $I)
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

        $I->sendGET('/v1/videos');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $data = $I->grabDataFromResponseByJsonPath('$.data.items[0]');

        $I->assertEquals(1, $data[0]['id']);
        $I->assertEquals('https://www.youtube.com/watch?v=YvG6D0qJflk', $data[0]['video_url']);
    }

    public function getAdminListDeletedDontShowTest(ApiTester $I)
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
            'status' => -1,
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => 1557803314,
            'updated_at' => 1557803314,
        ]);

        $I->amStaff('admin');

        $I->sendGET('/v1/videos');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeHttpHeader('X-Pagination-Total-Count', 0);
    }

    public function getStaffKabKotaListDeletedDontShowTest(ApiTester $I)
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
            'status' => -1,
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => 1557803314,
            'updated_at' => 1557803314,
        ]);

        $I->amStaff('staffkabkota');

        $I->sendGET('/v1/videos');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeHttpHeader('X-Pagination-Total-Count', 0);
    }

    public function getUserListDeletedDontShowTest(ApiTester $I)
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
            'status' => -1,
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => 1557803314,
            'updated_at' => 1557803314,
        ]);

        $I->amUser('staffrw');

        $I->sendGET('/v1/videos');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeHttpHeader('X-Pagination-Total-Count', 0);
    }

    public function getUserRWListDeletedDontShowTest(ApiTester $I)
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
            'status' => -1,
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => 1557803314,
            'updated_at' => 1557803314,
        ]);

        $I->amUser('staffrw');

        $I->sendGET('/v1/videos');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeHttpHeader('X-Pagination-Total-Count', 0);
    }

    public function getStaffKabKotaCascadeListTest(ApiTester $I)
    {
        $I->haveInDatabase('videos', [
            'id' => 1,
            'category_id' => 22,
            'title' => 'Lorem ipsum.',
            'source' => 'youtube',
            'video_url' => 'https://www.youtube.com/watch?v=lorem',
            'kabkota_id' => null,
            'total_likes' => 0,
            'seq' => null,
            'status' => 10,
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => 1557803314,
            'updated_at' => 1557803314,
        ]);

        $I->haveInDatabase('videos', [
            'id' => 2,
            'category_id' => 22,
            'title' => 'Lorem ipsum.',
            'source' => 'youtube',
            'video_url' => 'https://www.youtube.com/watch?v=ipsum',
            'kabkota_id' => 23,
            'total_likes' => 0,
            'seq' => null,
            'status' => 10,
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => 1557803314,
            'updated_at' => 1557803314,
        ]);

        $I->haveInDatabase('videos', [
            'id' => 3,
            'category_id' => 22,
            'title' => 'Lorem ipsum.',
            'source' => 'youtube',
            'video_url' => 'https://www.youtube.com/watch?v=sitamet',
            'kabkota_id' => 22,
            'total_likes' => 0,
            'seq' => null,
            'status' => 10,
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => 1557803314,
            'updated_at' => 1557803314,
        ]);

        $I->amStaff('staffkabkota');

        $I->sendGET('/v1/videos');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $data = $I->grabDataFromResponseByJsonPath('$.data.items');

        $I->assertEquals(1, $data[0][0]['id']);
        $I->assertEquals('https://www.youtube.com/watch?v=lorem', $data[0][0]['video_url']);

        $I->assertEquals(3, $data[0][1]['id']);
        $I->assertEquals('https://www.youtube.com/watch?v=sitamet', $data[0][1]['video_url']);

        $I->seeHttpHeader('X-Pagination-Total-Count', 2);
    }

    public function getUserListSortByPriorityListAscTest(ApiTester $I)
    {
        $I->haveInDatabase('videos', [
            'id' => 1,
            'category_id' => 22,
            'title' => 'Lorem ipsum.',
            'source' => 'youtube',
            'video_url' => 'https://www.youtube.com/watch?v=lorem',
            'kabkota_id' => null,
            'total_likes' => 0,
            'seq' => null,
            'status' => 10,
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => 1557803314,
            'updated_at' => 1557803314,
        ]);

        $I->haveInDatabase('videos', [
            'id' => 2,
            'category_id' => 22,
            'title' => 'Lorem ipsum.',
            'source' => 'youtube',
            'video_url' => 'https://www.youtube.com/watch?v=ipsum',
            'kabkota_id' => 23,
            'total_likes' => 0,
            'seq' => 1,
            'status' => 10,
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => 1557803314,
            'updated_at' => 1557803314,
        ]);

        $I->haveInDatabase('videos', [
            'id' => 3,
            'category_id' => 22,
            'title' => 'Lorem ipsum.',
            'source' => 'youtube',
            'video_url' => 'https://www.youtube.com/watch?v=sitamet',
            'kabkota_id' => 22,
            'total_likes' => 0,
            'seq' => 2,
            'status' => 10,
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => 1557803314,
            'updated_at' => 1557803314,
        ]);

        $I->amUser('user');

        $I->sendGET('/v1/videos?sort_by=seq&sort_order=asc');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $data = $I->grabDataFromResponseByJsonPath('$.data.items');

        $I->assertEquals(2, $data[0][0]['id']);
        $I->assertEquals(1, $data[0][0]['seq']);

        $I->assertEquals(3, $data[0][1]['id']);
        $I->assertEquals(2, $data[0][1]['seq']);

        $I->assertEquals(1, $data[0][2]['id']);
        $I->assertEquals(null, $data[0][2]['seq']);

        $I->seeHttpHeader('X-Pagination-Total-Count', 3);
    }
}
