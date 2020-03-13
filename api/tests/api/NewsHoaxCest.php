<?php

class NewsHoaxCest
{
    private $endpoint = '/v1/news-hoax';

    public function getUserListOnlyActiveTest(ApiTester $I)
    {
        Yii::$app->db->createCommand()->checkIntegrity(false)->execute();
        Yii::$app->db->createCommand('TRUNCATE news_hoax')->execute();

        // ACTIVE
        $I->haveInDatabase('news_hoax', [
            'id'          => 1,
            'category_id' => 28,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 10,
        ]);

        // DELETED
        $I->haveInDatabase('news_hoax', [
            'id'          => 2,
            'category_id' => 28,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'cover_path'  => 'covers/test.jpg',
            'status'      => -1,
        ]);

        // DISABLED
        $I->haveInDatabase('news_hoax', [
            'id'          => 3,
            'category_id' => 28,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 0,
        ]);

        $I->amUser('staffrw');

        $I->sendGET($this->endpoint);
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

    public function getStaffListCanSeeAllTest(ApiTester $I)
    {
        // ACTIVE
        $I->haveInDatabase('news_hoax', [
            'id'          => 1,
            'category_id' => 28,
            'title'       => 'Active News Hoax',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 10,
        ]);

        // DELETED
        $I->haveInDatabase('news_hoax', [
            'id'          => 2,
            'category_id' => 28,
            'title'       => 'Deleted News Hoax',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'cover_path'  => 'covers/test.jpg',
            'status'      => -1,
        ]);

        // DISABLED
        $I->haveInDatabase('news_hoax', [
            'id'          => 3,
            'category_id' => 28,
            'title'       => 'Inactive News Hoax',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 0,
        ]);

        // staffSaberhoax
        $I->amStaff('saberhoax');

        $I->sendGET($this->endpoint);
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeHttpHeader('X-Pagination-Total-Count', 2);

        $data = $I->grabDataFromResponseByJsonPath('$.data.items');

        $I->assertEquals(1, $data[0][0]['id']);
        $I->assertEquals(3, $data[0][1]['id']);

        // gubernur
        $I->amStaff('gubernur');

        $I->sendGET($this->endpoint);
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);
    }

    public function getUserListFilterCategoryTest(ApiTester $I)
    {
        $I->haveInDatabase('news_hoax', [
            'id'          => 1,
            'category_id' => 28,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 10,
        ]);

        $I->haveInDatabase('news_hoax', [
            'id'          => 2,
            'category_id' => 29,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 10,
        ]);

        $I->amUser('staffrw');

        $I->sendGET("{$this->endpoint}?category_id=28");
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

    public function getUserListSearchTest(ApiTester $I)
    {
        $I->haveInDatabase('news_hoax', [
            'id'          => 1,
            'category_id' => 28,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 10,
        ]);

        $I->haveInDatabase('news_hoax', [
            'id'          => 2,
            'category_id' => 28,
            'title'       => 'Consectetur adipiscing elit.',
            'content'     => 'Consectetur adipiscing elit.',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 10,
        ]);

        $I->amUser('staffrw');

        $I->sendGET("{$this->endpoint}?search=lorem");
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

    public function getUserListSearchNotFoundTest(ApiTester $I)
    {
        $I->haveInDatabase('news_hoax', [
            'id'          => 1,
            'category_id' => 28,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 10,
        ]);

        $I->haveInDatabase('news_hoax', [
            'id'          => 2,
            'category_id' => 28,
            'title'       => 'Consectetur adipiscing elit.',
            'content'     => 'Consectetur adipiscing elit.',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 10,
        ]);

        $I->amUser('staffrw');

        $I->sendGET("{$this->endpoint}?search=jawa");
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeHttpHeader('X-Pagination-Total-Count', 0);
    }

    public function getUserCanShowTest(ApiTester $I)
    {
        // ACTIVE
        $I->haveInDatabase('news_hoax', [
            'id'          => 1,
            'category_id' => 28,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 10,
        ]);

        // DELETED
        $I->haveInDatabase('news_hoax', [
            'id'          => 2,
            'category_id' => 28,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'cover_path'  => 'covers/test.jpg',
            'status'      => -1,
        ]);

        // DISABLED
        $I->haveInDatabase('news_hoax', [
            'id'          => 3,
            'category_id' => 28,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 0,
        ]);

        $I->amUser('staffrw');

        $I->sendGET("{$this->endpoint}/1");
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->sendGET("{$this->endpoint}/2");
        $I->canSeeResponseCodeIs(404);
        $I->seeResponseIsJson();

        $I->sendGET("{$this->endpoint}/3");
        $I->canSeeResponseCodeIs(404);
        $I->seeResponseIsJson();
    }

    public function getStaffAndPimpinanCanShowTest(ApiTester $I)
    {
        // ACTIVE
        $I->haveInDatabase('news_hoax', [
            'id'          => 1,
            'category_id' => 28,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 10,
        ]);

        // DELETED
        $I->haveInDatabase('news_hoax', [
            'id'          => 2,
            'category_id' => 28,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'cover_path'  => 'covers/test.jpg',
            'status'      => -1,
        ]);

        // DISABLED
        $I->haveInDatabase('news_hoax', [
            'id'          => 3,
            'category_id' => 28,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 0,
        ]);

        // staffSaberhoax
        $I->amStaff('saberhoax');

        $I->sendGET("{$this->endpoint}/1");
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->sendGET("{$this->endpoint}/2");
        $I->canSeeResponseCodeIs(404);
        $I->seeResponseIsJson();

        $I->sendGET("{$this->endpoint}/3");
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        // pimpinan
        $I->amStaff('gubernur');

        $I->sendGET("{$this->endpoint}/1");
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();
    }

    public function postUserCreateUnauthorizedTest(ApiTester $I)
    {
        $I->amUser('staffrw');

        $data = [];

        $I->sendPOST($this->endpoint, $data);
        $I->canSeeResponseCodeIs(403);
        $I->seeResponseIsJson();
    }

    public function postStaffCreateTest(ApiTester $I)
    {
        $I->amStaff('saberhoax');

        $data = [
            'category_id' => 28,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 10,
        ];

        $I->sendPOST($this->endpoint, $data);
        $I->canSeeResponseCodeIs(201);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 201,
        ]);

        $I->seeInDatabase('news_hoax', [
            'category_id' => 28,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 10,
        ]);
    }

    public function postStaffAdminCreateTest(ApiTester $I)
    {
        $I->amStaff();

        $data = [
            'category_id' => 28,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 10,
        ];

        $I->sendPOST($this->endpoint, $data);
        $I->canSeeResponseCodeIs(201);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 201,
        ]);

        $I->seeInDatabase('news_hoax', [
            'category_id' => 28,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 10,
        ]);
    }

    public function postUserUpdateUnauthorizedTest(ApiTester $I)
    {
        $I->haveInDatabase('news_hoax', [
            'id'          => 1,
            'category_id' => 28,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 10,
        ]);

        $I->amUser('staffrw');

        $data = [];

        $I->sendPUT("{$this->endpoint}/1", $data);
        $I->canSeeResponseCodeIs(403);
        $I->seeResponseIsJson();
    }

    public function postStaffUpdateTest(ApiTester $I)
    {
        $I->haveInDatabase('news_hoax', [
            'id'          => 1,
            'category_id' => 28,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 10,
        ]);

        $I->amStaff('saberhoax');

        $data = [
            'title'       => 'Lorem ipsum edited',
        ];

        $I->sendPUT("{$this->endpoint}/1", $data);
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeInDatabase('news_hoax', [
            'id'          => 1,
            'category_id' => 28,
            'title'       => 'Lorem ipsum edited',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 10,
        ]);
    }

    public function postStaffAdminUpdateTest(ApiTester $I)
    {
        $I->haveInDatabase('news_hoax', [
            'id'          => 1,
            'category_id' => 28,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 10,
        ]);

        $I->amStaff();

        $data = [
            'title'       => 'Lorem ipsum edited',
        ];

        $I->sendPUT("{$this->endpoint}/1", $data);
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeInDatabase('news_hoax', [
            'id'          => 1,
            'category_id' => 28,
            'title'       => 'Lorem ipsum edited',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 10,
        ]);
    }

    public function deleteUserUnauthorizedTest(ApiTester $I)
    {
        $I->amUser('staffrw');

        $I->sendDELETE("{$this->endpoint}/1");
        $I->canSeeResponseCodeIs(403);
    }

    public function deleteStaffTest(ApiTester $I)
    {
        $I->haveInDatabase('news_hoax', [
            'id'          => 1,
            'category_id' => 28,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 10,
        ]);

        $I->amStaff('saberhoax');

        $I->sendDELETE("{$this->endpoint}/1");
        $I->canSeeResponseCodeIs(204);

        $I->seeInDatabase('news_hoax', ['id' => 1, 'status' => -1]);
    }

    public function deleteStaffAdminTest(ApiTester $I)
    {
        $I->haveInDatabase('news_hoax', [
            'id'          => 1,
            'category_id' => 28,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 10,
        ]);

        $I->amStaff();

        $I->sendDELETE("{$this->endpoint}/1");
        $I->canSeeResponseCodeIs(204);

        $I->seeInDatabase('news_hoax', ['id' => 1, 'status' => -1]);
    }
}
