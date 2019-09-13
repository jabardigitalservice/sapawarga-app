<?php

class NewsFeaturedCest
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

    public function getUserListFeaturedActiveTest(ApiTester $I)
    {
        // ACTIVE
        $I->haveInDatabase('news', [
            'id'          => 1,
            'channel_id'  => 1,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'slug'        => 'lorem-ipsum-dolor-sit-amet-consectetur-adipiscing-elit',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'featured'    => false,
            'source_date' => '2019-06-20',
            'source_url'  => 'https://google.com',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 10,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        // DELETED
        $I->haveInDatabase('news', [
            'id'          => 3,
            'channel_id'  => 1,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'slug'        => 'lorem-ipsum-dolor-sit-amet-consectetur-adipiscing-elit',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'featured'    => false,
            'source_date' => '2019-06-20',
            'source_url'  => 'https://google.com',
            'cover_path'  => 'covers/test.jpg',
            'status'      => -1,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        // DISABLED
        $I->haveInDatabase('news', [
            'id'          => 4,
            'channel_id'  => 1,
            'title'       => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'slug'        => 'lorem-ipsum-dolor-sit-amet-consectetur-adipiscing-elit',
            'content'     => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin quam et libero fringilla, eget varius nunc hendrerit.',
            'featured'    => false,
            'source_date' => '2019-06-20',
            'source_url'  => 'https://google.com',
            'cover_path'  => 'covers/test.jpg',
            'status'      => 0,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->amUser('staffrw');

        $I->sendGET('/v1/news/featured');
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

    public function getUserListFeaturedKabkotaTest(ApiTester $I)
    {
        //
    }
}
