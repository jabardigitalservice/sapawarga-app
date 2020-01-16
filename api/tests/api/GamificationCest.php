<?php

use app\models\Like;
class NewsCest
{
    public function _before(ApiTester $I)
    {
        Yii::$app->db->createCommand()->checkIntegrity(false)->execute();

        Yii::$app->db->createCommand('TRUNCATE gamifications')->execute();
    }

    public function getUserListOnlyActiveTest(ApiTester $I)
    {
        // ACTIVE
        $I->haveInDatabase('gamifications', [
            'id'               => 1,
            'title'            => 'Misi membaca 10 berita',
            'title_badge'      => 'RW terupdate',
            'description'      => 'Didalam misi ini anda akan diajak untuk membaca beberapa berita sebanyak yang telah ditentukan, reward dari misi ini akan akan mendapatkan lencana/badge RW TERUPDATE',
            'object_type'      => 'news',
            'object_event'     => 'view_news_detail',
            'total_hit'        => 10,
            'image_badge_path' => 'http://localhost:81/storage/gamifications/image.jpg',
            'start_date'       => '2020-06-01',
            'end_date'         => '2020-06-20',
            'status'           => 10,
            'created_at'       => 1579160246,
            'updated_at'       => 1579160246,
            'created_by'       => 1,
            'updated_by'       => 1
        ]);

        $I->haveInDatabase('gamifications', [
            'id'               => 2,
            'title'            => 'Misi membaca 11 berita',
            'title_badge'      => 'RW terupdate',
            'description'      => 'Didalam misi ini anda akan diajak untuk membaca beberapa berita sebanyak yang telah ditentukan, reward dari misi ini akan akan mendapatkan lencana/badge RW TERUPDATE',
            'object_type'      => 'news',
            'object_event'     => 'view_news_detail',
            'total_hit'        => 10,
            'image_badge_path' => 'http://localhost:81/storage/gamifications/image.jpg',
            'start_date'       => '2020-06-01',
            'end_date'         => '2020-06-20',
            'status'           => -1,
            'created_at'       => 1579160246,
            'updated_at'       => 1579160246,
            'created_by'       => 1,
            'updated_by'       => 1
        ]);

        $I->amUser('staffrw');

        $I->sendGET('/v1/gamifications');
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

    public function postUserCreateUnauthorizedTest(ApiTester $I)
    {
        $I->amUser('staffrw');

        $data = [];

        $I->sendPOST('/v1/gamifications', $data);
        $I->canSeeResponseCodeIs(403);
        $I->seeResponseIsJson();
    }

    public function postAdminCreateTest(ApiTester $I)
    {
        $I->amStaff();

        $data = [
            'title'            => 'Misi membaca 10 berita',
            'title_badge'      => 'RW terupdate',
            'description'      => 'Didalam misi ini anda akan diajak untuk membaca beberapa berita sebanyak yang telah ditentukan, reward dari misi ini akan akan mendapatkan lencana/badge RW TERUPDATE',
            'object_type'      => 'news',
            'object_event'     => 'view_news_detail',
            'total_hit'        => 10,
            'image_badge_path' => 'http://localhost:81/storage/gamifications/image.jpg',
            'start_date'       => '2020-06-01',
            'end_date'         => '2020-06-20',
            'status'           => 10,
        ];

        $I->sendPOST('/v1/gamifications', $data);
        $I->canSeeResponseCodeIs(201);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 201,
        ]);

        $I->seeInDatabase('gamifications', [
            'id'               => 1,
            'title'            => 'Misi membaca 10 berita',
            'title_badge'      => 'RW terupdate',
            'description'      => 'Didalam misi ini anda akan diajak untuk membaca beberapa berita sebanyak yang telah ditentukan, reward dari misi ini akan akan mendapatkan lencana/badge RW TERUPDATE',
            'object_type'      => 'news',
            'object_event'     => 'view_news_detail',
            'total_hit'        => 10,
            'image_badge_path' => 'http://localhost:81/storage/gamifications/image.jpg',
            'start_date'       => '2020-06-01',
            'end_date'         => '2020-06-20',
            'status'           => 10,
        ]);
    }

    public function deleteUserUnauthorizedTest(ApiTester $I)
    {
        $I->amUser('staffrw');

        $I->sendDELETE('/v1/gamifications/1');
        $I->canSeeResponseCodeIs(403);
    }

    public function deleteAdminTest(ApiTester $I)
    {
        $I->haveInDatabase('gamifications', [
            'id'               => 1,
            'title'            => 'Misi membaca 10 berita',
            'title_badge'      => 'RW terupdate',
            'description'      => 'Didalam misi ini anda akan diajak untuk membaca beberapa berita sebanyak yang telah ditentukan, reward dari misi ini akan akan mendapatkan lencana/badge RW TERUPDATE',
            'object_type'      => 'news',
            'object_event'     => 'view_news_detail',
            'total_hit'        => 10,
            'image_badge_path' => 'http://localhost:81/storage/gamifications/image.jpg',
            'start_date'       => '2020-06-01',
            'end_date'         => '2020-06-20',
            'status'           => 10,
            'created_at'       => 1579160246,
            'updated_at'       => 1579160246,
            'created_by'       => 1,
            'updated_by'       => 1
        ]);

        $I->amStaff();

        $I->sendDELETE('/v1/gamifications/1');
        $I->canSeeResponseCodeIs(204);

        $I->seeInDatabase('gamifications', ['id' => 1, 'status' => -1]);
    }
}
