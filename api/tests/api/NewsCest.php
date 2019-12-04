<?php

class NewsCest
{
    public function _before(ApiTester $I)
    {
        Yii::$app->db->createCommand()->checkIntegrity(false)->execute();

        Yii::$app->db->createCommand('TRUNCATE news')->execute();
        Yii::$app->db->createCommand('TRUNCATE news_channels')->execute();

        $I->haveInDatabase('news_channels', [
            'id'         => 1,
            'name'       => 'Detik',
            'created_at' => '1554706345',
            'updated_at' => '1554706345',
        ]);
    }

    public function getUserListOnlyActiveTest(ApiTester $I)
    {
        // ACTIVE
        $I->haveInDatabase('news', [
            'id'          => 1,
            'channel_id'  => 1,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'slug'        => 'lorem-ipsum-dolor-sit-amet-consectetur-adipiscing-elit',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'source_date' => '2019-06-20',
            'source_url'  => 'https://google.com',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 10,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        // DELETED
        $I->haveInDatabase('news', [
            'id'          => 2,
            'channel_id'  => 1,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'slug'        => 'lorem-ipsum-dolor-sit-amet-consectetur-adipiscing-elit',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'source_date' => '2019-06-20',
            'source_url'  => 'https://google.com',
            'cover_path'  => 'covers/test.jpg',
            'status'      => -1,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        // DISABLED
        $I->haveInDatabase('news', [
            'id'          => 3,
            'channel_id'  => 1,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'slug'        => 'lorem-ipsum-dolor-sit-amet-consectetur-adipiscing-elit',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'source_date' => '2019-06-20',
            'source_url'  => 'https://google.com',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 0,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->amUser('staffrw');

        $I->sendGET('/v1/news');
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

    public function getAdminListCanSeeAllTest(ApiTester $I)
    {
        // ACTIVE, KABKOTA_ID NULL
        $I->haveInDatabase('news', [
            'id'          => 1,
            'channel_id'  => 1,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'slug'        => 'lorem-ipsum-dolor-sit-amet-consectetur-adipiscing-elit',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'kabkota_id'  => null,
            'source_date' => '2019-06-20',
            'source_url'  => 'https://google.com',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 10,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        // ACTIVE, KABKOTA_ID 22
        $I->haveInDatabase('news', [
            'id'          => 2,
            'channel_id'  => 1,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'slug'        => 'lorem-ipsum-dolor-sit-amet-consectetur-adipiscing-elit',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'kabkota_id'  => 22,
            'source_date' => '2019-06-20',
            'source_url'  => 'https://google.com',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 10,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        // ACTIVE, KABKOTA_ID 14
        $I->haveInDatabase('news', [
            'id'          => 3,
            'channel_id'  => 1,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'slug'        => 'lorem-ipsum-dolor-sit-amet-consectetur-adipiscing-elit',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'kabkota_id'  => 14,
            'source_date' => '2019-06-20',
            'source_url'  => 'https://google.com',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 10,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        // DELETED
        $I->haveInDatabase('news', [
            'id'          => 4,
            'channel_id'  => 1,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'slug'        => 'lorem-ipsum-dolor-sit-amet-consectetur-adipiscing-elit',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'kabkota_id'  => null,
            'source_date' => '2019-06-20',
            'source_url'  => 'https://google.com',
            'cover_path'  => 'covers/test.jpg',
            'status'      => -1,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        // DISABLED
        $I->haveInDatabase('news', [
            'id'          => 5,
            'channel_id'  => 1,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'slug'        => 'lorem-ipsum-dolor-sit-amet-consectetur-adipiscing-elit',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'source_date' => '2019-06-20',
            'source_url'  => 'https://google.com',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 0,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->amStaff();

        $I->sendGET('/v1/news');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeHttpHeader('X-Pagination-Total-Count', 4);

        $data = $I->grabDataFromResponseByJsonPath('$.data.items');

        $I->assertEquals(1, $data[0][0]['id']);
        $I->assertEquals(2, $data[0][1]['id']);
        $I->assertEquals(3, $data[0][2]['id']);
        $I->assertEquals(5, $data[0][3]['id']);


        $I->amStaff('staffkabkota');

        $I->sendGET('/v1/news');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeHttpHeader('X-Pagination-Total-Count', 3);

        $data = $I->grabDataFromResponseByJsonPath('$.data.items');

        $I->assertEquals(1, $data[0][0]['id']);
        $I->assertEquals(2, $data[0][1]['id']);
        $I->assertEquals(5, $data[0][2]['id']);
    }

    public function getUserListFilterChannelTest(ApiTester $I)
    {
        $I->haveInDatabase('news_channels', [
            'id'         => 2,
            'name'       => 'Kompas',
            'created_at' => '1554706345',
            'updated_at' => '1554706345',
        ]);

        $I->haveInDatabase('news', [
            'id'          => 1,
            'channel_id'  => 1,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'slug'        => 'lorem-ipsum-dolor-sit-amet-consectetur-adipiscing-elit',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'source_date' => '2019-06-20',
            'source_url'  => 'https://google.com',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 10,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->haveInDatabase('news', [
            'id'          => 2,
            'channel_id'  => 2,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'slug'        => 'lorem-ipsum-dolor-sit-amet-consectetur-adipiscing-elit',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'source_date' => '2019-06-20',
            'source_url'  => 'https://google.com',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 10,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->amUser('staffrw');

        $I->sendGET('/v1/news?channel_id=1');
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

    public function getUserListFilterKabkotaTest(ApiTester $I)
    {
        $I->haveInDatabase('news_channels', [
            'id'         => 2,
            'name'       => 'Kompas',
            'created_at' => '1554706345',
            'updated_at' => '1554706345',
        ]);

        $I->haveInDatabase('news', [
            'id'          => 1,
            'channel_id'  => 1,
            'kabkota_id'  => null,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'slug'        => 'lorem-ipsum-dolor-sit-amet-consectetur-adipiscing-elit',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'source_date' => '2019-06-20',
            'source_url'  => 'https://google.com',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 10,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->haveInDatabase('news', [
            'id'          => 2,
            'channel_id'  => 2,
            'kabkota_id'  => 22,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'slug'        => 'lorem-ipsum-dolor-sit-amet-consectetur-adipiscing-elit',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'source_date' => '2019-06-20',
            'source_url'  => 'https://google.com',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 10,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->amUser('staffrw');

        $I->sendGET('/v1/news?kabkota_id=22');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeHttpHeader('X-Pagination-Total-Count', 1);

        $data = $I->grabDataFromResponseByJsonPath('$.data.items[0]');

        $I->assertEquals(2, $data[0]['id']);
    }

    public function getUserListFilterChannelNotFoundTest(ApiTester $I)
    {
        $I->haveInDatabase('news', [
            'id'          => 1,
            'channel_id'  => 1,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'slug'        => 'lorem-ipsum-dolor-sit-amet-consectetur-adipiscing-elit',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'source_date' => '2019-06-20',
            'source_url'  => 'https://google.com',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 10,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->amUser('staffrw');

        $I->sendGET('/v1/news?channel_id=2');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeHttpHeader('X-Pagination-Total-Count', 0);
    }

    public function getUserListSearchTest(ApiTester $I)
    {
        $I->haveInDatabase('news', [
            'id'          => 1,
            'channel_id'  => 1,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'slug'        => 'lorem-ipsum-dolor-sit-amet-consectetur-adipiscing-elit',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'source_date' => '2019-06-20',
            'source_url'  => 'https://google.com',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 10,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->haveInDatabase('news', [
            'id'          => 2,
            'channel_id'  => 1,
            'title'       => 'Consectetur adipiscing elit.',
            'slug'        => 'consectetur-adipiscing-elit',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'source_date' => '2019-06-20',
            'source_url'  => 'https://google.com',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 10,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->amUser('staffrw');

        $I->sendGET('/v1/news?search=lorem');
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
        $I->haveInDatabase('news', [
            'id'          => 1,
            'channel_id'  => 1,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'slug'        => 'lorem-ipsum-dolor-sit-amet-consectetur-adipiscing-elit',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'source_date' => '2019-06-20',
            'source_url'  => 'https://google.com',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 10,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->haveInDatabase('news', [
            'id'          => 2,
            'channel_id'  => 1,
            'title'       => 'Consectetur adipiscing elit.',
            'slug'        => 'consectetur-adipiscing-elit',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'source_date' => '2019-06-20',
            'source_url'  => 'https://google.com',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 10,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->amUser('staffrw');

        $I->sendGET('/v1/news?search=jawa');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeHttpHeader('X-Pagination-Total-Count', 0);
    }

    public function getStaffListSearchAnotherAreaTest(ApiTester $I)
    {
        $I->haveInDatabase('news', [
            'id'          => 1,
            'channel_id'  => 1,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'slug'        => 'lorem-ipsum-dolor-sit-amet-consectetur-adipiscing-elit',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla',
            'source_date' => '2019-06-20',
            'source_url'  => 'https://google.com',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 10,
            'kabkota_id'  => 22,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->haveInDatabase('news', [
            'id'          => 2,
            'channel_id'  => 1,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'slug'        => 'lorem-ipsum-dolor-sit-amet-consectetur-adipiscing-elit',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla',
            'source_date' => '2019-06-20',
            'source_url'  => 'https://google.com',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 10,
            'kabkota_id'  => 23,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->amStaff('staffkabkota2');

        $I->sendGET('/v1/news?search=lorem');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeHttpHeader('X-Pagination-Total-Count', 1);

        $data = $I->grabDataFromResponseByJsonPath('$.data.items[0]');

        $I->assertEquals(2, $data[0]['id']);
    }

    public function getStaffProvSearchTitleTest(ApiTester $I)
    {
        $I->haveInDatabase('news', [
            'id'          => 1,
            'channel_id'  => 1,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'slug'        => 'lorem-ipsum-dolor-sit-amet-consectetur-adipiscing-elit',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla',
            'source_date' => '2019-06-20',
            'source_url'  => 'https://google.com',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 10,
            'kabkota_id'  => 22,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->haveInDatabase('news', [
            'id'          => 2,
            'channel_id'  => 1,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'slug'        => 'lorem-ipsum-dolor-sit-amet-consectetur-adipiscing-elit',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla',
            'source_date' => '2019-06-20',
            'source_url'  => 'https://google.com',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 10,
            'kabkota_id'  => 23,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->amStaff('staffprov');

        $I->sendGET('/v1/news?search=lorem');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeHttpHeader('X-Pagination-Total-Count', 2);
    }

    public function getStaffProvSearchTitleAndAreaTest(ApiTester $I)
    {
        $I->haveInDatabase('news', [
            'id'          => 1,
            'channel_id'  => 1,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'slug'        => 'lorem-ipsum-dolor-sit-amet-consectetur-adipiscing-elit',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla',
            'source_date' => '2019-06-20',
            'source_url'  => 'https://google.com',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 10,
            'kabkota_id'  => 22,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->haveInDatabase('news', [
            'id'          => 2,
            'channel_id'  => 1,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'slug'        => 'lorem-ipsum-dolor-sit-amet-consectetur-adipiscing-elit',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla',
            'source_date' => '2019-06-20',
            'source_url'  => 'https://google.com',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 10,
            'kabkota_id'  => 23,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->amStaff('staffprov');

        $I->sendGET('/v1/news?search=lorem&kabkota_id=22');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeHttpHeader('X-Pagination-Total-Count', 1);

        $data = $I->grabDataFromResponseByJsonPath('$.data.items[0]');

        $I->assertEquals(22, $data[0]['kabkota_id']);
        $I->assertEquals(1, $data[0]['id']);
    }

    public function getUserCanShowTest(ApiTester $I)
    {
        // ACTIVE
        $I->haveInDatabase('news', [
            'id'          => 1,
            'channel_id'  => 1,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'slug'        => 'lorem-ipsum-dolor-sit-amet-consectetur-adipiscing-elit',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'source_date' => '2019-06-20',
            'source_url'  => 'https://google.com',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 10,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        // DELETED
        $I->haveInDatabase('news', [
            'id'          => 2,
            'channel_id'  => 1,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'slug'        => 'lorem-ipsum-dolor-sit-amet-consectetur-adipiscing-elit',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'source_date' => '2019-06-20',
            'source_url'  => 'https://google.com',
            'cover_path'  => 'covers/test.jpg',
            'status'      => -1,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        // DISABLED
        $I->haveInDatabase('news', [
            'id'          => 3,
            'channel_id'  => 1,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'slug'        => 'lorem-ipsum-dolor-sit-amet-consectetur-adipiscing-elit',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'source_date' => '2019-06-20',
            'source_url'  => 'https://google.com',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 0,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->amUser('staffrw');

        $I->sendGET('/v1/news/1');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->sendGET('/v1/news/2');
        $I->canSeeResponseCodeIs(404);
        $I->seeResponseIsJson();

        $I->sendGET('/v1/news/3');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();
    }

    public function getUserListRelatedExcludeTest(ApiTester $I)
    {
        $I->haveInDatabase('news', [
            'id'          => 1,
            'channel_id'  => 1,
            'kabkota_id'  => null,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'slug'        => 'lorem-ipsum-dolor-sit-amet-consectetur-adipiscing-elit',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'source_date' => '2019-06-20',
            'source_url'  => 'https://google.com',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 10,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->haveInDatabase('news', [
            'id'          => 2,
            'channel_id'  => 1,
            'kabkota_id'  => null,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'slug'        => 'lorem-ipsum-dolor-sit-amet-consectetur-adipiscing-elit',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'source_date' => '2019-06-20',
            'source_url'  => 'https://google.com',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 10,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->amUser('staffrw');

        $I->sendGET('/v1/news/related?id=1');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeHttpHeader('X-Pagination-Total-Count', 1);

        $data = $I->grabDataFromResponseByJsonPath('$.data.items[0]');

        $I->assertEquals(2, $data[0]['id']);
    }

    public function getAdminCanShowTest(ApiTester $I)
    {
        // ACTIVE
        $I->haveInDatabase('news', [
            'id'          => 1,
            'channel_id'  => 1,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'slug'        => 'lorem-ipsum-dolor-sit-amet-consectetur-adipiscing-elit',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'source_date' => '2019-06-20',
            'source_url'  => 'https://google.com',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 10,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        // DELETED
        $I->haveInDatabase('news', [
            'id'          => 2,
            'channel_id'  => 1,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'slug'        => 'lorem-ipsum-dolor-sit-amet-consectetur-adipiscing-elit',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'source_date' => '2019-06-20',
            'source_url'  => 'https://google.com',
            'cover_path'  => 'covers/test.jpg',
            'status'      => -1,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        // DISABLED
        $I->haveInDatabase('news', [
            'id'          => 3,
            'channel_id'  => 1,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'slug'        => 'lorem-ipsum-dolor-sit-amet-consectetur-adipiscing-elit',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'source_date' => '2019-06-20',
            'source_url'  => 'https://google.com',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 0,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->amUser('staffrw');

        $I->sendGET('/v1/news/1');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->sendGET('/v1/news/2');
        $I->canSeeResponseCodeIs(404);
        $I->seeResponseIsJson();

        $I->sendGET('/v1/news/3');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();
    }

    public function postUserCreateUnauthorizedTest(ApiTester $I)
    {
        $I->amUser('staffrw');

        $data = [];

        $I->sendPOST('/v1/news', $data);
        $I->canSeeResponseCodeIs(403);
        $I->seeResponseIsJson();
    }

    public function postAdminCreateTest(ApiTester $I)
    {
        $I->amStaff();

        $data = [
            'title'       => 'Lorem ipsum',
            'channel_id'  => 1,
            'kabkota_id'  => 22,
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'source_date' => '2019-06-20',
            'source_url'  => 'https://google.com',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 0,
        ];

        $I->sendPOST('/v1/news', $data);
        $I->canSeeResponseCodeIs(201);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 201,
        ]);

        $I->seeInDatabase('news', [
            'title'       => 'Lorem ipsum',
            'channel_id'  => 1,
            'kabkota_id'  => 22,
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'source_date' => '2019-06-20',
            'source_url'  => 'https://google.com',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 0,
        ]);
    }

    public function postUserUpdateUnauthorizedTest(ApiTester $I)
    {
        $I->haveInDatabase('news', [
            'id'          => 1,
            'channel_id'  => 1,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'slug'        => 'lorem-ipsum-dolor-sit-amet-consectetur-adipiscing-elit',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'source_date' => '2019-06-20',
            'source_url'  => 'https://google.com',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 10,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->amUser('staffrw');

        $data = [];

        $I->sendPUT('/v1/news/1', $data);
        $I->canSeeResponseCodeIs(403);
        $I->seeResponseIsJson();
    }

    public function postAdminUpdateTest(ApiTester $I)
    {
        $I->haveInDatabase('news', [
            'id'          => 1,
            'channel_id'  => 1,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'slug'        => 'lorem-ipsum-dolor-sit-amet-consectetur-adipiscing-elit',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'source_date' => '2019-06-20',
            'source_url'  => 'https://google.com',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 10,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
            'created_by'  => 1,
            'updated_by'  => 1,
        ]);

        $I->amStaff();

        $data = [
            'title'       => 'Lorem ipsum',
            'channel_id'  => 1,
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'source_date' => '2019-06-20',
            'source_url'  => 'https://google.com',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 0,
        ];

        $I->sendPUT('/v1/news/1', $data);
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeInDatabase('news', [
            'title'       => 'Lorem ipsum',
            'channel_id'  => 1,
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'source_date' => '2019-06-20',
            'source_url'  => 'https://google.com',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 0,
        ]);
    }

    public function postUpdateNotOwnNewsfailTest(ApiTester $I)
    {
        $I->haveInDatabase('news', [
            'id'          => 1,
            'channel_id'  => 1,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'slug'        => 'lorem-ipsum-dolor-sit-amet-consectetur-adipiscing-elit',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'source_date' => '2019-06-20',
            'source_url'  => 'https://google.com',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 10,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
            'created_by'  => 2,
            'updated_by'  => 2,
        ]);

        $I->amStaff();

        $data = [
            'title'       => 'Lorem ipsum',
            'channel_id'  => 1,
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'source_date' => '2019-06-20',
            'source_url'  => 'https://google.com',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 0,
        ];

        $I->sendPUT('/v1/news/1', $data);
        $I->canSeeResponseCodeIs(403);
    }

    public function deleteUserUnauthorizedTest(ApiTester $I)
    {
        $I->amUser('staffrw');

        $I->sendDELETE('/v1/news/1');
        $I->canSeeResponseCodeIs(403);
    }

    public function deleteAdminTest(ApiTester $I)
    {
        $I->haveInDatabase('news', [
            'id'          => 1,
            'channel_id'  => 1,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'slug'        => 'lorem-ipsum-dolor-sit-amet-consectetur-adipiscing-elit',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'source_date' => '2019-06-20',
            'source_url'  => 'https://google.com',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 10,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
            'created_by'  => 1,
            'updated_by'  => 1,
        ]);

        $I->amStaff();

        $I->sendDELETE('/v1/news/1');
        $I->canSeeResponseCodeIs(204);

        $I->seeInDatabase('news', ['id' => 1, 'status' => -1]);
    }

    public function deleteNotOwnNewsFailTest(ApiTester $I)
    {
        $I->haveInDatabase('news', [
            'id'          => 1,
            'channel_id'  => 1,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'slug'        => 'lorem-ipsum-dolor-sit-amet-consectetur-adipiscing-elit',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'source_date' => '2019-06-20',
            'source_url'  => 'https://google.com',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 10,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
            'created_by'  => 2,
            'updated_by'  => 2,
        ]);

        $I->amStaff();

        $I->sendDELETE('/v1/news/1');
        $I->canSeeResponseCodeIs(403);
    }

    public function getUserIncrementReadCountTest(ApiTester $I)
    {
        $read_count = 0;
        $I->haveInDatabase('news', [
            'id'          => 1,
            'channel_id'  => 1,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'slug'        => 'lorem-ipsum-dolor-sit-amet-consectetur-adipiscing-elit',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'source_date' => '2019-06-20',
            'source_url'  => 'https://google.com',
            'cover_path'  => 'covers/test.jpg',
            'meta'        => json_encode(['read_count' => $read_count]),
            'status'      => 10,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->amUser('staffrw');

        $I->sendGET('/v1/news/1');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $new_read_count = $I->grabDataFromResponseByJsonPath('$.data.total_viewers');

        $I->assertEquals($read_count + 1, $new_read_count[0]);
    }

//    public function getUserIncrementReadCountPerUserForNewUserTest(ApiTester $I)
//    {
//        $I->seeNumRecords(0, 'news_viewers');
//
//        $read_count = 0;
//        $I->haveInDatabase('news', [
//            'id'            => 1,
//            'channel_id'    => 1,
//            'title'         => 'persib',
//            'slug'          => 'lorem-ipsum-dolor-sit-amet-consectetur-adipiscing-elit',
//            'content'       => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
//            'source_date'   => '2019-06-20',
//            'source_url'    => 'https://google.com',
//            'cover_path'    => 'covers/test.jpg',
//            'total_viewers' => $read_count,
//            'status'        => 10,
//            'created_at'    => '1554706345',
//            'updated_at'    => '1554706345',
//        ]);
//
//        $I->amUser('staffrw');
//
//        $user = $I->grabDataFromResponseByJsonPath('$.data');
//
//        $I->sendGET('/v1/news/1');
//        $I->canSeeResponseCodeIs(200);
//        $I->seeResponseIsJson();
//
//        $I->seeResponseContainsJson([
//            'success' => true,
//            'status'  => 200,
//        ]);
//
//        $I->seeNumRecords(1, 'news_viewers', ['news_id' => 1, 'user_id' => $user[0]['id'], 'read_count' => $read_count + 1]);
//
//        $new_read_count = $I->grabDataFromResponseByJsonPath('$.data.total_viewers');
//
//        $I->assertEquals($read_count + 1, $new_read_count[0]);
//    }

//    public function getUserIncrementReadCountPerUserForExistUserTest(ApiTester $I)
//    {
//        $read_count = 10;
//
//        $I->haveInDatabase('news', [
//            'id'            => 1,
//            'channel_id'    => 1,
//            'title'         => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
//            'slug'          => 'lorem-ipsum-dolor-sit-amet-consectetur-adipiscing-elit',
//            'content'       => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
//            'source_date'   => '2019-06-20',
//            'source_url'    => 'https://google.com',
//            'cover_path'    => 'covers/test.jpg',
//            'total_viewers' => $read_count,
//            'status'        => 10,
//            'created_at'    => '1554706345',
//            'updated_at'    => '1554706345',
//        ]);
//
//        $I->amUser('staffrw');
//
//        $user = $I->grabDataFromResponseByJsonPath('$.data');
//
//        $I->haveInDatabase('news_viewers', [
//            'news_id'     => 1,
//            'user_id'     => $user[0]['id'],
//            'read_count'  => $read_count,
//        ]);
//
//        $I->sendGET('/v1/news/1');
//        $I->canSeeResponseCodeIs(200);
//        $I->seeResponseIsJson();
//
//        $I->seeResponseContainsJson([
//            'success' => true,
//            'status'  => 200,
//        ]);
//
//        $I->seeNumRecords(1, 'news_viewers', ['news_id' => 1, 'user_id' => $user[0]['id'], 'read_count' => $read_count + 1]);
//
//        $new_read_count = $I->grabDataFromResponseByJsonPath('$.data.total_viewers');
//
//        $I->assertEquals($read_count + 1, $new_read_count[0]);
//    }

    public function getUserStatisticsUnauthorizedTest(ApiTester $I)
    {
        $I->amUser('staffrw');

        $I->sendGET('/v1/news/statistics');
        $I->canSeeResponseCodeIs(403);
    }

    public function getAdminStatisticsTest(ApiTester $I)
    {
        $I->haveInDatabase('news_channels', [
            'id'         => 2,
            'name'       => 'Kompas',
            'created_at' => '1554706345',
            'updated_at' => '1554706345',
        ]);


        $I->haveInDatabase('news', [
            'id'          => 1,
            'channel_id'  => 1,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'slug'        => 'lorem-ipsum-dolor-sit-amet-consectetur-adipiscing-elit',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'source_date' => '2019-06-20',
            'source_url'  => 'https://google.com',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 10,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->amStaff();

        $I->sendGET('/v1/news/statistics');
        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeHttpHeader('X-Pagination-Total-Count', 2);

        $data = $I->grabDataFromResponseByJsonPath('$.data.items');

        $I->assertEquals(1, $data[0][0]['id']);
        $I->assertEquals(1, $data[0][0]['count']);
        $I->assertEquals(2, $data[0][1]['id']);
        $I->assertEquals(0, $data[0][1]['count']);
    }
}
