<?php

use Carbon\Carbon;
use app\models\Gamification;

class GamificationCest
{
    public function _before(ApiTester $I)
    {
        Yii::$app->db->createCommand()->checkIntegrity(false)->execute();

        Yii::$app->db->createCommand('TRUNCATE gamifications')->execute();
        Yii::$app->db->createCommand('TRUNCATE gamification_participants')->execute();
        Yii::$app->db->createCommand('TRUNCATE gamification_activities')->execute();

        Yii::$app->db->createCommand('TRUNCATE news_channels')->execute();
        Yii::$app->db->createCommand('TRUNCATE news')->execute();
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
            'object_event'     => 'news_view_detail',
            'total_hit'        => 10,
            'image_badge_path' => 'http://localhost:81/storage/gamifications/image.jpg',
            'start_date'       => (new Carbon())->toDateString(),
            'end_date'         => (new Carbon())->addDays(7)->toDateString(),
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
            'object_event'     => 'news_view_detail',
            'total_hit'        => 10,
            'image_badge_path' => 'http://localhost:81/storage/gamifications/image.jpg',
            'start_date'       => (new Carbon())->toDateString(),
            'end_date'         => (new Carbon())->addDays(7)->toDateString(),
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
            'object_event'     => 'news_view_detail',
            'total_hit'        => 10,
            'image_badge_path' => 'http://localhost:81/storage/gamifications/image.jpg',
            'start_date'       => (new Carbon())->toDateString(),
            'end_date'         => (new Carbon())->addDays(7)->toDateString(),
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
            'object_event'     => 'news_view_detail',
            'total_hit'        => 10,
            'image_badge_path' => 'http://localhost:81/storage/gamifications/image.jpg',
            'start_date'       => (new Carbon())->toDateString(),
            'end_date'         => (new Carbon())->addDays(7)->toDateString(),
            'status'           => 10,
        ]);
    }

    public function postAdminUpdateTest(ApiTester $I)
    {
        $I->haveInDatabase('gamifications', [
            'id'               => 1,
            'title'            => 'Misi membaca 10 berita',
            'title_badge'      => 'RW terupdate',
            'description'      => 'Didalam misi ini anda akan diajak untuk membaca beberapa berita sebanyak yang telah ditentukan, reward dari misi ini akan akan mendapatkan lencana/badge RW TERUPDATE',
            'object_type'      => 'news',
            'object_event'     => 'news_view_detail',
            'total_hit'        => 10,
            'image_badge_path' => 'http://localhost:81/storage/gamifications/image.jpg',
            'start_date'       => (new Carbon())->toDateString(),
            'end_date'         => (new Carbon())->addDays(7)->toDateString(),
            'status'           => 10,
            'created_at'       => 1579160246,
            'updated_at'       => 1579160246,
            'created_by'       => 1,
            'updated_by'       => 1,
        ]);

        $I->amStaff();

        $data = [
            'id'               => 1,
            'title'            => 'Update Misi membaca 10 berita',
            'title_badge'      => 'Update RW terupdate',
            'description'      => 'Update Didalam misi ini anda akan diajak untuk membaca beberapa berita sebanyak yang telah ditentukan, reward dari misi ini akan akan mendapatkan lencana/badge RW TERUPDATE',
            'object_type'      => 'news',
            'object_event'     => 'news_view_detail',
            'total_hit'        => 10,
            'image_badge_path' => 'http://localhost:81/storage/gamifications/imageupdate.jpg',
            'start_date'       => (new Carbon())->toDateString(),
            'end_date'         => (new Carbon())->addDays(10)->toDateString(),
            'status'           => 10,
        ];

        $I->sendPUT('/v1/gamifications/1', $data);
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeInDatabase('gamifications', [
            'id'               => 1,
            'title'            => 'Update Misi membaca 10 berita',
            'title_badge'      => 'Update RW terupdate',
            'description'      => 'Update Didalam misi ini anda akan diajak untuk membaca beberapa berita sebanyak yang telah ditentukan, reward dari misi ini akan akan mendapatkan lencana/badge RW TERUPDATE',
            'object_type'      => 'news',
            'object_event'     => 'news_view_detail',
            'total_hit'        => 10,
            'image_badge_path' => 'http://localhost:81/storage/gamifications/imageupdate.jpg',
            'start_date'       => (new Carbon())->toDateString(),
            'end_date'         => (new Carbon())->addDays(10)->toDateString(),
            'status'           => 10,
        ]);
    }

    public function userJoinGamificationNoDatafail(ApiTester $I)
    {
        $I->amUser('staffrw');

        $I->sendPOST('/v1/gamifications/join/11111');
        $I->canSeeResponseCodeIs(404);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => false,
            'status'  => 404,
        ]);
    }

    public function userJoinGamificationSuccess(ApiTester $I)
    {
        $I->haveInDatabase('gamifications', [
            'id'               => 1,
            'title'            => 'Misi membaca 10 berita',
            'title_badge'      => 'RW terupdate',
            'description'      => 'Didalam misi ini anda akan diajak untuk membaca beberapa berita sebanyak yang telah ditentukan, reward dari misi ini akan akan mendapatkan lencana/badge RW TERUPDATE',
            'object_type'      => 'news',
            'object_event'     => 'news_view_detail',
            'total_hit'        => 10,
            'image_badge_path' => 'http://localhost:81/storage/gamifications/image.jpg',
            'start_date'       => (new Carbon())->toDateString(),
            'end_date'         => (new Carbon())->addDays(7)->toDateString(),
            'status'           => 10,
            'created_at'       => 1579160246,
            'updated_at'       => 1579160246,
            'created_by'       => 1,
            'updated_by'       => 1
        ]);

        $I->amUser('staffrw');

        $I->sendPOST('/v1/gamifications/join/1');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);
    }

    public function userSaveGamificationActivitySuccess(ApiTester $I)
    {
        $I->haveInDatabase('gamifications', [
            'id'               => 1,
            'title'            => 'Misi membaca 3 berita',
            'title_badge'      => 'RW terupdate',
            'description'      => 'Didalam misi ini anda akan diajak untuk membaca beberapa berita sebanyak yang telah ditentukan, reward dari misi ini akan akan mendapatkan lencana/badge RW TERUPDATE',
            'object_type'      => 'news',
            'object_event'     => 'news_view_detail',
            'total_hit'        => 3,
            'image_badge_path' => 'http://localhost:81/storage/gamifications/image.jpg',
            'start_date'       => (new Carbon())->toDateString(),
            'end_date'         => (new Carbon())->addDays(7)->toDateString(),
            'status'           => 10,
            'created_at'       => 1579160246,
            'updated_at'       => 1579160246,
            'created_by'       => 1,
            'updated_by'       => 1
        ]);

        $I->amUser('staffrw');

        $I->sendPOST('/v1/gamifications/join/1');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        // Get action
        $I->haveInDatabase('news_channels', [
            'id'         => 1,
            'name'       => 'Detik',
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

        $I->sendGET('/v1/news/1');
        $I->canSeeResponseCodeIs(200);

        $I->seeInDatabase('gamification_participants', [
            'id' => 1,
            'gamification_id' => 1,
            'user_id' => 17,
            'total_user_hit' => 1,
        ]);

        $I->seeInDatabase('gamification_activities', [
            'id' => 1,
            'gamification_id' => 1,
            'object_id' => 1,
            'user_id' => 17,
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
            'object_event'     => 'news_view_detail',
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
