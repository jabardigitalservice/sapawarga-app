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

    public function getUserListTest(ApiTester $I)
    {
        $I->amUser('user');

        $I->sendGET('/v1/video');
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

        $I->sendGET('/v1/video');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
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
            'seq' => 0,
            'status' => 10,
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => 1557803314,
            'updated_at' => 1557803314,
        ]);

        $I->amUser('user');

        $I->sendGET('/v1/video');
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
            'seq' => 0,
            'status' => 10,
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => 1557803314,
            'updated_at' => 1557803314,
        ]);

        $I->amUser('staffrw');

        $I->sendGET('/v1/video');
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
            'seq' => 0,
            'status' => 10,
            'created_by' => 1,
            'updated_by' => 1,
            'created_at' => 1557803314,
            'updated_at' => 1557803314,
        ]);

        $I->amStaff('admin');

        $I->sendGET('/v1/video');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $data = $I->grabDataFromResponseByJsonPath('$.data.items[0]');

        $I->assertEquals(1, $data[0]['id']);
    }

}
