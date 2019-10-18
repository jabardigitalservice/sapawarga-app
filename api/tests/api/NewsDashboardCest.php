<?php
use Carbon\Carbon;

class NewsDashboardCest
{
    public function _before(ApiTester $I)
    {
        Yii::$app->db->createCommand()->checkIntegrity(false)->execute();
        Yii::$app->db->createCommand('TRUNCATE news')->execute();
    }

    public function getNewsMostLikeProvinceTest(ApiTester $I)
    {
        $todayDate = Carbon::now()->timestamp;

        $I->haveInDatabase('news', [
            'id'            => 1,
            'channel_id'    => 1,
            'title'         => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'slug'          => 'lorem-ipsum-dolor-sit-amet-consectetur-adipiscing-elit',
            'content'       => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin.',
            'source_date'   => '2019-06-20',
            'source_url'    => 'https://google.com',
            'cover_path'    => 'covers/test.jpg',
            'status'        => 10,
            'total_viewers' => 50,
            'created_at'    => $todayDate,
            'updated_at'    => $todayDate,

        ]);

        $I->haveInDatabase('news', [
            'id'            => 2,
            'channel_id'    => 1,
            'title'         => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'slug'          => 'lorem-ipsum-dolor-sit-amet-consectetur-adipiscing-elit',
            'content'       => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin.',
            'source_date'   => '2019-06-20',
            'source_url'    => 'https://google.com',
            'cover_path'    => 'covers/test.jpg',
            'status'        => 10,
            'total_viewers' => 100,
            'created_at'    => $todayDate,
            'updated_at'    => $todayDate,
        ]);

        $I->haveInDatabase('news', [
            'id'            => 3,
            'channel_id'    => 1,
            'title'         => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'slug'          => 'lorem-ipsum-dolor-sit-amet-consectetur-adipiscing-elit',
            'content'       => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin.',
            'source_date'   => '2019-06-20',
            'source_url'    => 'https://google.com',
            'cover_path'    => 'covers/test.jpg',
            'status'        => 10,
            'total_viewers' => 50,
            'kabkota_id'    => 22,
            'created_at'    => $todayDate,
            'updated_at'    => $todayDate,
        ]);

        $I->amStaff();

        $I->sendGET('/v1/dashboards/news-most-likes?location=province');
        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $data = $I->grabDataFromResponseByJsonPath('$.data');

        $I->assertEquals(2, count($data[0]));
        $I->assertEquals(100, $data[0][0]['total_viewers']);
        $I->assertEquals(50, $data[0][1]['total_viewers']);
    }


    public function getNewsMostLikeKabKotaTest(ApiTester $I)
    {
        $todayDate = Carbon::now()->timestamp;

        $I->haveInDatabase('news', [
            'id'            => 1,
            'channel_id'    => 1,
            'title'         => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'slug'          => 'lorem-ipsum-dolor-sit-amet-consectetur-adipiscing-elit',
            'content'       => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin.',
            'source_date'   => '2019-06-20',
            'source_url'    => 'https://google.com',
            'cover_path'    => 'covers/test.jpg',
            'status'        => 10,
            'total_viewers' => 50,
            'created_at'    => $todayDate,
            'updated_at'    => $todayDate,

        ]);

        $I->haveInDatabase('news', [
            'id'            => 2,
            'channel_id'    => 1,
            'title'         => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'slug'          => 'lorem-ipsum-dolor-sit-amet-consectetur-adipiscing-elit',
            'content'       => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin.',
            'source_date'   => '2019-06-20',
            'source_url'    => 'https://google.com',
            'cover_path'    => 'covers/test.jpg',
            'status'        => 10,
            'total_viewers' => 55,
            'kabkota_id'    => 22,
            'created_at'    => $todayDate,
            'updated_at'    => $todayDate,
        ]);

        $I->haveInDatabase('news', [
            'id'            => 3,
            'channel_id'    => 1,
            'title'         => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.',
            'slug'          => 'lorem-ipsum-dolor-sit-amet-consectetur-adipiscing-elit',
            'content'       => 'Maecenas porttitor suscipit ex vitae hendrerit. Nunc sollicitudin.',
            'source_date'   => '2019-06-20',
            'source_url'    => 'https://google.com',
            'cover_path'    => 'covers/test.jpg',
            'status'        => 10,
            'total_viewers' => 110,
            'kabkota_id'    => 22,
            'created_at'    => $todayDate,
            'updated_at'    => $todayDate,
        ]);

        $I->amStaff();

        $I->sendGET('/v1/dashboards/news-most-likes?location=kabkota');
        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $data = $I->grabDataFromResponseByJsonPath('$.data');

        $I->assertEquals(2, count($data[0]));
        $I->assertEquals(110, $data[0][0]['total_viewers']);
        $I->assertEquals(55, $data[0][1]['total_viewers']);
    }


    public function _after(ApiTester $I)
    {
        Yii::$app->db->createCommand()->checkIntegrity(false)->execute();
        Yii::$app->db->createCommand('TRUNCATE news')->execute();
    }
}
