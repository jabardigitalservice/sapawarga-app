<?php

class BannerCest
{
    public function _before(ApiTester $I)
    {
        Yii::$app->db->createCommand()->checkIntegrity(false)->execute();
        Yii::$app->db->createCommand('TRUNCATE banners')->execute();
    }

    public function getBannerListNotAllowedUserTest(ApiTester $I)
    {
        $I->amStaff('staffkabkota');

        $I->sendGET('/v1/banners');

        $I->canSeeResponseCodeIs(403);
        $I->seeResponseIsJson();
    }

    public function getBannerListTest(ApiTester $I)
    {
        $I->amStaff('staffprov');

        $I->sendGET('/v1/banners');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();
    }

    public function postUserCreateUnauthorizedTest(ApiTester $I)
    {
        $I->amStaff('staffkabkota');

        $data = [];

        $I->sendPOST('/v1/banners', $data);
        $I->canSeeResponseCodeIs(403);
        $I->seeResponseIsJson();
    }

    public function postAdminCreateBannerTest(ApiTester $I)
    {
        $I->amStaff();

        $data = [
            'title' => 'Lorem ipsum dolor sit amet',
            'image_path' => 'https://cdn.images.com',
            'type' => 'external',
            'link_url' => 'https://google.com/',
            'status' => 10,
        ];

        $I->sendPOST('/v1/banners', $data);
        $I->canSeeResponseCodeIs(201);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 201,
        ]);

        $I->seeInDatabase('banners', [
            'id' => 1,
            'title' => 'Lorem ipsum dolor sit amet',
            'image_path' => 'https://cdn.images.com',
            'type' => 'external',
            'link_url' => 'https://google.com/',
            'status' => 10,
        ]);
    }

    public function postAdminCreateInternalBannerTest(ApiTester $I)
    {
        $I->amStaff('staffprov');

        $data = [
            'title' => 'Lorem ipsum dolor sit amet',
            'image_path' => 'https://cdn.images.com',
            'type' => 'internal',
            'internal_category' => 'news',
            'internal_entity_id' => 1,
            'internal_entity_name' => 'Judul News',
            'link_url' => 'https://google.com/',
            'status' => 10,
        ];

        $I->sendPOST('/v1/banners', $data);
        $I->canSeeResponseCodeIs(201);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 201,
        ]);

        $I->seeInDatabase('banners', [
            'title' => 'Lorem ipsum dolor sit amet',
            'image_path' => 'https://cdn.images.com',
            'type' => 'internal',
            'internal_category' => 'news',
            'internal_entity_id' => 1,
            'internal_entity_name' => 'Judul News',
            'link_url' => 'https://google.com/',
            'status' => 10,
        ]);
    }


    public function postUserUpdateUnauthorizedTest(ApiTester $I)
    {
        $I->haveInDatabase('banners', [
            'id' =>1,
            'title' =>'Banner ulang tahun Jawa Barat',
            'image_path' =>'https://cdn.images.com',
            'type' =>'external',
            'link_url' =>'https://news.detik.com/berita-datang-ke-mk',
            'internal_category' =>null,
            'internal_entity_id' =>null,
            'status' => 10,
            'created_at' =>1570085479,
            'updated_at' =>1570085479,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        $I->amUser('staffrw');

        $data = [];

        $I->sendPUT('/v1/banners/1', $data);
        $I->canSeeResponseCodeIs(403);
        $I->seeResponseIsJson();
    }

    public function deleteUserUnauthorizedTest(ApiTester $I)
    {
        $I->amUser('staffrw');

        $I->sendDELETE('/v1/banners/1');
        $I->canSeeResponseCodeIs(403);
    }

    public function deleteAdminTest(ApiTester $I)
    {
        $I->haveInDatabase('banners', [
            'id' =>1,
            'title' =>'Banner ulang tahun Jawa Barat',
            'image_path' =>'https://cdn.images.com',
            'type' =>'external',
            'link_url' =>'https://news.detik.com/berita-datang-ke-mk',
            'internal_category' =>null,
            'internal_entity_id' =>null,
            'status' => 10,
            'created_at' =>1570085479,
            'updated_at' =>1570085479,
            'created_by' => 1,
            'updated_by' => 1
        ]);

        $I->amStaff();

        $I->sendDELETE('/v1/banners/1');
        $I->canSeeResponseCodeIs(204);

        $I->seeInDatabase('banners', ['id' => 1, 'status' => -1]);
    }
}
