<?php

class NewsImportantCest
{
    public function _before(ApiTester $I)
    {
        Yii::$app->db->createCommand()->checkIntegrity(false)->execute();
        Yii::$app->db->createCommand('TRUNCATE news_important')->execute();
        Yii::$app->db->createCommand('TRUNCATE news_important_attachment')->execute();
    }

    protected function loadData(ApiTester $I)
    {
        $I->haveInDatabase('news_important', [
            'id' => 1,
            'title' => 'Info Pendidikan',
            'content' => 'Info Pendidikan',
            'category_id' => 36,
            'status' => 10,
            'created_at' =>1570085479,
            'updated_at' =>1570085479,
            'created_by' => 42,
            'updated_by' => 42
        ]);

        $I->haveInDatabase('news_important', [
            'id' => 2,
            'title' => 'Info Lowongan Kerja',
            'content' => 'Info Lowongan Kerja',
            'category_id' => 37,
            'status' => 10,
            'created_at' =>1570085479,
            'updated_at' =>1570085479,
            'created_by' => 43,
            'updated_by' => 43
        ]);

        $I->haveInDatabase('news_important', [
            'id' => 3,
            'title' => 'Info Inactive',
            'content' => 'Info Inactive',
            'category_id' => 37,
            'status' => 0,
            'created_at' =>1570085479,
            'updated_at' =>1570085479,
            'created_by' => 43,
            'updated_by' => 43
        ]);

        $I->haveInDatabase('news_important', [
            'id' => 4,
            'title' => 'Info Deleted',
            'content' => 'Info Deleted',
            'category_id' => 37,
            'status' => -1,
            'created_at' =>1570085479,
            'updated_at' =>1570085479,
            'created_by' => 43,
            'updated_by' => 43
        ]);

        $I->haveInDatabase('news_important', [
            'id' => 5,
            'title' => 'Info Pendidikan Kota Bandung',
            'content' => 'Info Pendidikan Kota Bandung',
            'category_id' => 36,
            'kabkota_id' => 22,
            'status' => 10,
            'created_at' =>1570085479,
            'updated_at' =>1570085479,
            'created_by' => 42,
            'updated_by' => 42
        ]);

        $I->haveInDatabase('news_important', [
            'id' => 6,
            'title' => 'Info Pendidikan Kota Bekasi',
            'content' => 'Info Pendidikan Kota Bekasi',
            'category_id' => 36,
            'kabkota_id' => 23,
            'status' => 10,
            'created_at' =>1570085479,
            'updated_at' =>1570085479,
            'created_by' => 42,
            'updated_by' => 42
        ]);
    }

    public function getNewsImportantListNotAllowedUserTest(ApiTester $I)
    {
        $I->amStaff('staffkabkota');

        $I->sendGET('/v1/news-important');

        $I->canSeeResponseCodeIs(403);
        $I->seeResponseIsJson();
    }

    /**
     * @before loadData
     */
    public function getUserNewsImportantListTest(ApiTester $I)
    {
        // Search by RW Kota Bandung
        $I->amUser('user.bandung');

        $I->sendGET('/v1/news-important');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeHttpHeader('X-Pagination-Total-Count', 3);
        $data = $I->grabDataFromResponseByJsonPath('$.data.items');
        $I->assertEquals('Info Pendidikan', $data[0][0]['title']);
        $I->assertEquals('Info Lowongan Kerja', $data[0][1]['title']);
        $I->assertEquals('Info Pendidikan Kota Bandung', $data[0][2]['title']);

        // Search by RW Kota Bekasi
        $I->amUser('user.bekasi');

        $I->sendGET('/v1/news-important');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeHttpHeader('X-Pagination-Total-Count', 3);
        $data = $I->grabDataFromResponseByJsonPath('$.data.items');
        $I->assertEquals('Info Pendidikan', $data[0][0]['title']);
        $I->assertEquals('Info Lowongan Kerja', $data[0][1]['title']);
        $I->assertEquals('Info Pendidikan Kota Bekasi', $data[0][2]['title']);
    }

    /**
     * @before loadData
     */
    public function getStaffNewsImportantListTest(ApiTester $I)
    {
        $I->amStaff('staffprov');
        $I->sendGET('/v1/news-important');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->amStaff('opd.disdik');
        $I->sendGET('/v1/news-important');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeHttpHeader('X-Pagination-Total-Count', 5);
        $data = $I->grabDataFromResponseByJsonPath('$.data.items');
        $I->assertEquals('Info Pendidikan', $data[0][0]['title']);
        $I->assertEquals('Info Lowongan Kerja', $data[0][1]['title']);
        $I->assertEquals('Info Inactive', $data[0][2]['title']);
        $I->assertEquals('Info Pendidikan Kota Bandung', $data[0][3]['title']);
        $I->assertEquals('Info Pendidikan Kota Bekasi', $data[0][4]['title']);
    }

    /**
     * @before loadData
     */
    public function getNewsImportantListSearchByNameTest(ApiTester $I)
    {
        // Search by OPD
        $I->amStaff('opd.disdik');

        $I->sendGET('/v1/news-important?search=Pendidikan');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeHttpHeader('X-Pagination-Total-Count', 3);
        $data = $I->grabDataFromResponseByJsonPath('$.data.items[0]');
        $I->assertEquals('Info Pendidikan', $data[0]['title']);

        // Search by RW
        $I->amUser('staffrw');

        $I->sendGET('/v1/news-important?search=Info');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeHttpHeader('X-Pagination-Total-Count', 3);
        $data = $I->grabDataFromResponseByJsonPath('$.data.items');
        $I->assertEquals('Info Pendidikan', $data[0][0]['title']);
        $I->assertEquals('Info Lowongan Kerja', $data[0][1]['title']);
    }

    /**
     * @before loadData
     */
    public function getNewsImportantListFilterByCategoryTest(ApiTester $I)
    {
        // Filter by OPD
        $I->amStaff('opd.disdik');
        $I->sendGET('/v1/news-important?category_id=36');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeHttpHeader('X-Pagination-Total-Count', 3);
        $data = $I->grabDataFromResponseByJsonPath('$.data.items[0]');
        $I->assertEquals(36, $data[0]['category_id']);

        // Filter by RW
        $I->amStaff('staffrw');
        $I->sendGET('/v1/news-important?category_id=37');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeHttpHeader('X-Pagination-Total-Count', 1);
        $data = $I->grabDataFromResponseByJsonPath('$.data.items[0]');
        $I->assertEquals(37, $data[0]['category_id']);
    }

    /**
     * @before loadData
     */
    public function getNewsImportantListFilterByStatusTest(ApiTester $I)
    {
        $I->amStaff('opd.disnakertrans');
        $I->sendGET('/v1/news-important?status=0');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeHttpHeader('X-Pagination-Total-Count', 1);
        $data = $I->grabDataFromResponseByJsonPath('$.data.items[0]');
        $I->assertEquals(0, $data[0]['status']);
    }

    /**
     * @before loadData
     */
    public function getNewsImportantListFilterByKabkotaTest(ApiTester $I)
    {
        $I->amStaff('opd.disnakertrans');
        $I->sendGET('/v1/news-important?kabkota_id=22');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeHttpHeader('X-Pagination-Total-Count', 1);
        $data = $I->grabDataFromResponseByJsonPath('$.data.items[0]');
        $I->assertEquals(22, $data[0]['kabkota_id']);
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

    public function postStaffOPDCreateNewsImportantTest(ApiTester $I)
    {
        $I->amStaff('opd.disdik');

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

    /**
     * @before loadData
     */
    public function postUpdateNotOwnUnauthorizedTest(ApiTester $I)
    {
        $I->amUser('opd.disdik');
        $data = [];
        $I->sendPUT('/v1/news-important/2', $data);
        $I->canSeeResponseCodeIs(403);
        $I->seeResponseIsJson();

        $I->amUser('opd.disnakertrans');
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

    /**
     * @before loadData
     */
    public function deleteNotOwnUnauthorizedTest(ApiTester $I)
    {
        $I->amUser('opd.disdik');

        $I->sendDELETE('/v1/news-important/2');
        $I->canSeeResponseCodeIs(403);

        $I->amUser('opd.disnakertrans');

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
