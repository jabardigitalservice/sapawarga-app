<?php
use Carbon\Carbon;

class NewsImportantCest
{
    public function _before(ApiTester $I)
    {
        Yii::$app->db->createCommand()->checkIntegrity(false)->execute();
        Yii::$app->db->createCommand('TRUNCATE news_important')->execute();
        Yii::$app->db->createCommand('TRUNCATE news_important_attachment')->execute();
    }

    public function getNewsImportantListNotAllowedUserTest(ApiTester $I)
    {
        $I->amStaff('staffkabkota');

        $I->sendGET('/v1/news-important');

        $I->canSeeResponseCodeIs(403);
        $I->seeResponseIsJson();
    }

    public function getNewsImportantListTest(ApiTester $I)
    {
        $I->amStaff('staffprov');

        $I->sendGET('/v1/news-important');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();
    }

    public function postUserCreateUnauthorizedTest(ApiTester $I)
    {
        $I->amStaff('staffkabkota');

        $data = [];

        $I->sendPOST('/v1/news-important', $data);
        $I->canSeeResponseCodeIs(403);
        $I->seeResponseIsJson();
    }

    public function postAdminCreateNewsImportantTest(ApiTester $I)
    {
        $I->amStaff();

        $data = [
            'title' => 'Lorem ipsum dolor sit amet',
            'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'source_url' =>  'https://google.com/',
            'image_path' => 'general/myimage.jpg',
            'category_id' => 2,
            'status' => 10,
            'attachments' => []
        ];

        $I->sendPOST('/v1/news-important', $data);
        $I->canSeeResponseCodeIs(201);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 201,
        ]);

        $I->seeInDatabase('news_important', [
            'id' => 1,
            'title' => 'Lorem ipsum dolor sit amet',
            'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'source_url' =>  'https://google.com/',
            'image_path' => 'general/myimage.jpg',
            'category_id' => 2,
            'status' => 10,
        ]);
    }

    public function postUserUpdateUnauthorizedTest(ApiTester $I)
    {
        $I->haveInDatabase('news_important', [
            'id' =>1,
            'title' =>'Lorem ipsum dolor sit amet',
            'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'source_url' => 'https://news.detik.com/berita-datang-ke-mk',
            'image_path' => 'general/myimage.jpg',
            'category_id' => 2,
            'status' => 10,
            'created_at' =>1570085479,
            'updated_at' =>1570085479,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        $I->amUser('staffrw');

        $data = [];

        $I->sendPUT('/v1/news-important/1', $data);
        $I->canSeeResponseCodeIs(403);
        $I->seeResponseIsJson();
    }

    public function deleteUserUnauthorizedTest(ApiTester $I)
    {
        $I->amUser('staffrw');

        $I->sendDELETE('/v1/news-important/1');
        $I->canSeeResponseCodeIs(403);
    }

    public function deleteAdminTest(ApiTester $I)
    {
        $I->haveInDatabase('news_important', [
            'id' =>1,
            'title' =>'Lorem ipsum dolor sit amet',
            'content' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'source_url' => 'https://news.detik.com/berita-datang-ke-mk',
            'image_path' => 'general/myimage.jpg',
            'category_id' => 2,
            'status' => 10,
            'created_at' =>1570085479,
            'updated_at' =>1570085479,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        $I->amStaff();

        $I->sendDELETE('/v1/news-important/1');
        $I->canSeeResponseCodeIs(204);

        $I->seeInDatabase('news_important', ['id' => 1, 'status' => -1]);
    }
}
