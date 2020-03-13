<?php
use Carbon\Carbon;

class PopupCest
{
    public function _before(ApiTester $I)
    {
        Yii::$app->db->createCommand()->checkIntegrity(false)->execute();
        Yii::$app->db->createCommand('TRUNCATE popups')->execute();
    }

    public function getPopupListNotAllowedUserTest(ApiTester $I)
    {
        $I->amStaff('staffkabkota');

        $I->sendGET('/v1/popups');

        $I->canSeeResponseCodeIs(403);
        $I->seeResponseIsJson();
    }

    public function getPopupListTest(ApiTester $I)
    {
        $I->amStaff('staffprov');

        $I->sendGET('/v1/popups');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();
    }

    public function postUserCreateUnauthorizedTest(ApiTester $I)
    {
        $I->amStaff('staffkabkota');

        $data = [];

        $I->sendPOST('/v1/popups', $data);
        $I->canSeeResponseCodeIs(403);
        $I->seeResponseIsJson();
    }

    public function postAdminCreatePopupTest(ApiTester $I)
    {
        $I->amStaff();

        $data = [
            'title' => 'Lorem ipsum dolor sit amet',
            'image_path' => 'https://cdn.images.com',
            'type' => 'external',
            'link_url' => 'https://google.com/',
            'status' => 10,
            'start_date'  => (new Carbon()),
            'end_date'    => (new Carbon())->addDays(7),
        ];

        $I->sendPOST('/v1/popups', $data);
        $I->canSeeResponseCodeIs(201);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 201,
        ]);

        $I->seeInDatabase('popups', [
            'id' => 1,
            'title' => 'Lorem ipsum dolor sit amet',
            'image_path' => 'https://cdn.images.com',
            'type' => 'external',
            'link_url' => 'https://google.com/',
            'status' => 10,
        ]);
    }

    public function postAdminCreateInternalPopupTest(ApiTester $I)
    {
        $I->amStaff('staffprov');

        $data = [
            'title' => 'Lorem ipsum dolor sit amet',
            'image_path' => 'https://cdn.images.com',
            'type' => 'internal',
            'internal_object_type' => 'news',
            'internal_object_id' => 1,
            'internal_object_name' => 'Judul News',
            'link_url' => '',
            'status' => 10,
            'start_date' => '2019-09-09 00:00:00',
            'end_date' => '2019-09-30 00:00:00',
        ];

        $I->sendPOST('/v1/popups', $data);
        $I->canSeeResponseCodeIs(201);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 201,
        ]);

        $I->seeInDatabase('popups', [
            'title' => 'Lorem ipsum dolor sit amet',
            'image_path' => 'https://cdn.images.com',
            'type' => 'internal',
            'internal_object_type' => 'news',
            'internal_object_id' => 1,
            'internal_object_name' => 'Judul News',
            'link_url' => '',
            'status' => 10,
            'start_date' => '2019-09-09 00:00:00',
            'end_date' => '2019-09-30 00:00:00',
        ]);
    }

    public function postExistRangeDateWithinTest(ApiTester $I)
    {
        $I->haveInDatabase('popups', [
            'id' =>1,
            'title' =>'Popup ulang tahun Jawa Barat',
            'image_path' =>'https://cdn.images.com',
            'type' =>'external',
            'link_url' =>'https://news.detik.com/berita-datang-ke-mk',
            'internal_object_type' =>null,
            'internal_object_id' =>null,
            'status' => 10,
            'start_date'  => (new Carbon()),
            'end_date'    => (new Carbon())->addDays(7),
            'created_at' =>1570085479,
            'updated_at' =>1570085479,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        $I->amStaff();

        $data = [
            'title' => 'Lorem ipsum dolor sit amet',
            'image_path' => 'https://cdn.images.com',
            'type' => 'internal',
            'internal_object_type' => 'news',
            'internal_object_id' => 1,
            'internal_object_name' => 'Judul News',
            'link_url' => 'https://google.com/',
            'status' => 10,
            'start_date'  => (new Carbon())->addDays(1),
            'end_date'    => (new Carbon())->addDays(8),
        ];

        $I->sendPOST('/v1/popups', $data);
        $I->canSeeResponseCodeIs(422);
        $I->seeResponseIsJson();
    }

    public function postUserUpdateUnauthorizedTest(ApiTester $I)
    {
        $I->haveInDatabase('popups', [
            'id' =>1,
            'title' =>'Popup ulang tahun Jawa Barat',
            'image_path' =>'https://cdn.images.com',
            'type' =>'external',
            'link_url' =>'https://news.detik.com/berita-datang-ke-mk',
            'internal_object_type' =>null,
            'internal_object_id' =>null,
            'status' => 10,
            'start_date'  => (new Carbon()),
            'end_date'    => (new Carbon())->addDays(7),
            'created_at' =>1570085479,
            'updated_at' =>1570085479,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        $I->amUser('staffrw');

        $data = [];

        $I->sendPUT('/v1/popups/1', $data);
        $I->canSeeResponseCodeIs(403);
        $I->seeResponseIsJson();
    }

    public function deleteUserUnauthorizedTest(ApiTester $I)
    {
        $I->amUser('staffrw');

        $I->sendDELETE('/v1/popups/1');
        $I->canSeeResponseCodeIs(403);
    }

    public function deleteAdminTest(ApiTester $I)
    {
        $I->haveInDatabase('popups', [
            'id' =>1,
            'title' =>'Popup ulang tahun Jawa Barat',
            'image_path' =>'https://cdn.images.com',
            'type' =>'external',
            'link_url' =>'https://news.detik.com/berita-datang-ke-mk',
            'internal_object_type' =>null,
            'internal_object_id' =>null,
            'status' => 10,
            'start_date'  => (new Carbon()),
            'end_date'    => (new Carbon())->addDays(7),
            'created_at' =>1570085479,
            'updated_at' =>1570085479,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        $I->amStaff();

        $I->sendDELETE('/v1/popups/1');
        $I->canSeeResponseCodeIs(204);

        $I->seeInDatabase('popups', ['id' => 1, 'status' => -1]);
    }
}
