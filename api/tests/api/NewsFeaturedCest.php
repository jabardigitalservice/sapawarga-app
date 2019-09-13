<?php

class NewsFeaturedCest
{
    public function _before(ApiTester $I)
    {
        Yii::$app->db->createCommand()->checkIntegrity(false)->execute();

        Yii::$app->db->createCommand('TRUNCATE news')->execute();
        Yii::$app->db->createCommand('TRUNCATE news_channels')->execute();
        Yii::$app->db->createCommand('TRUNCATE news_featured')->execute();

        $I->haveInDatabase('news_channels', [
            'id'         => 1,
            'name'       => 'Detik',
            'created_at' => '1554706345',
            'updated_at' => '1554706345',
        ]);

        // PROVINSI
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

        // KABKOTA
        // ACTIVE
        $I->haveInDatabase('news', [
            'id'          => 4,
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

        // DELETED
        $I->haveInDatabase('news', [
            'id'          => 5,
            'channel_id'  => 1,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'slug'        => 'lorem-ipsum-dolor-sit-amet-consectetur-adipiscing-elit',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'kabkota_id'  => 22,
            'source_date' => '2019-06-20',
            'source_url'  => 'https://google.com',
            'cover_path'  => 'covers/test.jpg',
            'status'      => -1,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        // DISABLED
        $I->haveInDatabase('news', [
            'id'          => 6,
            'channel_id'  => 1,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'slug'        => 'lorem-ipsum-dolor-sit-amet-consectetur-adipiscing-elit',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'kabkota_id'  => 22,
            'source_date' => '2019-06-20',
            'source_url'  => 'https://google.com',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 0,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->haveInDatabase('news_featured', [
            'news_id' => 1,
            'seq'     => 1,
        ]);

        $I->haveInDatabase('news_featured', [
            'news_id'    => 4,
            'kabkota_id' => 22,
            'seq'        => 1,
        ]);
    }

    public function getUserListFeaturedProvinsiTest(ApiTester $I)
    {
        $I->amUser('staffrw');

        $I->sendGET('/v1/news/featured');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $data = $I->grabDataFromResponseByJsonPath('$.data');

        $I->assertEquals(1, $data[0][0]['id']);
        $I->assertNull($data[0][1]);
        $I->assertNull($data[0][2]);
        $I->assertNull($data[0][3]);
        $I->assertNull($data[0][4]);
    }

    public function getUserListFeaturedKabkotaTest(ApiTester $I)
    {
        $I->amUser('staffrw');

        $I->sendGET('/v1/news/featured?kabkota_id=22');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $data = $I->grabDataFromResponseByJsonPath('$.data');

        $I->assertEquals(4, $data[0][0]['id']);
        $I->assertNull($data[0][1]);
        $I->assertNull($data[0][2]);
        $I->assertNull($data[0][3]);
        $I->assertNull($data[0][4]);
    }
}
