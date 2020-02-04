<?php

use app\models\Aspirasi;

class AspirasiCest
{
    public function _before(ApiTester $I)
    {
        Yii::$app->db->createCommand()->checkIntegrity(false)->execute();
        Yii::$app->db->createCommand('TRUNCATE aspirasi_likes')->execute();
        Yii::$app->db->createCommand('TRUNCATE aspirasi')->execute();
    }

    public function getUserListTest(ApiTester $I)
    {
        $I->amUser('user');

        $I->sendGET('/v1/aspirasi');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);
    }

    public function getUserListDefaultSortTest(ApiTester $I)
    {
        $I->haveInDatabase('aspirasi', [
            'id'          => 1,
            'title'       => 'Lorem ipsum',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'kabkota_id'  => 22,
            'kec_id'      => 446,
            'kel_id'      => 6082,
            'status'      => 10,
            'category_id' => 9, // INFRASTRUKTUR
            'author_id'   => 36,
            'created_at'  => 1553010000,
            'updated_at'  => 1553010000,
        ]);

        $I->haveInDatabase('aspirasi', [
            'id'          => 2,
            'title'       => 'Lorem ipsum',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'kabkota_id'  => 22,
            'kec_id'      => 446,
            'kel_id'      => 6082,
            'status'      => 10,
            'category_id' => 10, // SUMBER DAYA MANUSIA
            'author_id'   => 36,
            'created_at'  => 1553020000,
            'updated_at'  => 1553020000,
        ]);

        $I->haveInDatabase('aspirasi', [
            'id'          => 3,
            'title'       => 'Lorem ipsum',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'kabkota_id'  => 22,
            'kec_id'      => 446,
            'kel_id'      => 6082,
            'status'      => 10,
            'category_id' => 11, // EKONOMI
            'author_id'   => 36,
            'created_at'  => 1553030000,
            'updated_at'  => 1553030000,
        ]);

        $I->amUser('user');

        $I->sendGET('/v1/aspirasi');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $data = $I->grabDataFromResponseByJsonPath('$.data.items');

        $I->assertEquals(3, $data[0][0]['id']);
        $I->assertEquals(2, $data[0][1]['id']);
        $I->assertEquals(1, $data[0][2]['id']);
    }

    public function getUserListOrderByCategoryNameAscendingTest(ApiTester $I)
    {
        $I->haveInDatabase('aspirasi', [
            'id'          => 1,
            'title'       => 'Lorem ipsum',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'kabkota_id'  => 22,
            'kec_id'      => 446,
            'kel_id'      => 6082,
            'status'      => 10,
            'category_id' => 9, // INFRASTRUKTUR
            'author_id'   => 36,
        ]);

        $I->haveInDatabase('aspirasi', [
            'id'          => 2,
            'title'       => 'Lorem ipsum',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'kabkota_id'  => 22,
            'kec_id'      => 446,
            'kel_id'      => 6082,
            'status'      => 10,
            'category_id' => 10, // SUMBER DAYA MANUSIA
            'author_id'   => 36,
        ]);

        $I->haveInDatabase('aspirasi', [
            'id'          => 3,
            'title'       => 'Lorem ipsum',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'kabkota_id'  => 22,
            'kec_id'      => 446,
            'kel_id'      => 6082,
            'status'      => 10,
            'category_id' => 11, // EKONOMI
            'author_id'   => 36,
        ]);

        $I->amUser('user');

        $I->sendGET('/v1/aspirasi?sort_by=category.name&sort_order=ascending');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $data = $I->grabDataFromResponseByJsonPath('$.data.items');

        $I->assertEquals(3, $data[0][0]['id']);
        $I->assertEquals(1, $data[0][1]['id']);
        $I->assertEquals(2, $data[0][2]['id']);
    }

    public function getUserListOrderByCategoryNameDescendingTest(ApiTester $I)
    {
        $I->haveInDatabase('aspirasi', [
            'id'          => 1,
            'title'       => 'Lorem ipsum',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'kabkota_id'  => 22,
            'kec_id'      => 446,
            'kel_id'      => 6082,
            'status'      => 10,
            'category_id' => 9, // INFRASTRUKTUR
            'author_id'   => 36,
        ]);

        $I->haveInDatabase('aspirasi', [
            'id'          => 2,
            'title'       => 'Lorem ipsum',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'kabkota_id'  => 22,
            'kec_id'      => 446,
            'kel_id'      => 6082,
            'status'      => 10,
            'category_id' => 10, // SUMBER DAYA MANUSIA
            'author_id'   => 36,
        ]);

        $I->haveInDatabase('aspirasi', [
            'id'          => 3,
            'title'       => 'Lorem ipsum',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'kabkota_id'  => 22,
            'kec_id'      => 446,
            'kel_id'      => 6082,
            'status'      => 10,
            'category_id' => 11, // EKONOMI
            'author_id'   => 36,
        ]);

        $I->amUser('user');

        $I->sendGET('/v1/aspirasi?sort_by=category.name&sort_order=descending');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $data = $I->grabDataFromResponseByJsonPath('$.data.items');

        $I->assertEquals(2, $data[0][0]['id']);
        $I->assertEquals(1, $data[0][1]['id']);
        $I->assertEquals(3, $data[0][2]['id']);
    }

    public function getShowTest(ApiTester $I)
    {
        $I->haveInDatabase('aspirasi', [
            'id'          => 1,
            'title'       => 'Lorem ipsum',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit,
            sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'kabkota_id'  => 22,
            'kec_id'      => 446,
            'kel_id'      => 6082,
            'status'      => 10,
            'category_id' => 9,
            'author_id'   => 36,
        ]);

        $I->amUser('user');

        $I->sendGET('/v1/aspirasi/1');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);
    }

    public function postUserCreateUnauthorizedTest(ApiTester $I)
    {
        $I->amUser('user');

        $data = [
            'id'          => 1,
            'title'       => 'Lorem ipsum',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit,
            sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'kabkota_id'  => 22,
            'kec_id'      => 446,
            'kel_id'      => 6082,
            'status'      => 0,
            'category_id' => 9,
            'author_id'   => 36,
        ];

        $I->sendPOST('/v1/aspirasi', $data);
        $I->canSeeResponseCodeIs(403);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => false,
            'status'  => 403,
        ]);
    }

    public function postUpdateTest(ApiTester $I)
    {
        $I->haveInDatabase('aspirasi', [
            'id'          => 1,
            'title'       => 'Lorem ipsum',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit,
            sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'kabkota_id'  => 22,
            'kec_id'      => 446,
            'kel_id'      => 6082,
            'status'      => 0,
            'category_id' => 9,
            'author_id'   => 36,
        ]);

        $I->amUser('user');

        $data = [
            'title'       => 'Lorem ipsum',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit,
            sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'kabkota_id'  => 22,
            'kec_id'      => 446,
            'kel_id'      => 6082,
            'status'      => 0,
            'category_id' => 9,
            'author_id'   => 36,
        ];

        $I->sendPUT('/v1/aspirasi/1', $data);
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);
    }

    public function postAdminCanUnpublishedTest(ApiTester $I)
    {
        $I->haveInDatabase('aspirasi', [
            'id'          => 1,
            'title'       => 'Lorem ipsum',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit,
            sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'kabkota_id'  => 22,
            'kec_id'      => 446,
            'kel_id'      => 6082,
            'status'      => 10,
            'category_id' => 9,
            'author_id'   => 36,
        ]);

        $I->amStaff();

        $data = [
            'status'      => 7,
        ];

        $I->sendPUT('/v1/aspirasi/1', $data);
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);
    }

    public function postAdminCanPublishedTest(ApiTester $I)
    {
        $I->haveInDatabase('aspirasi', [
            'id'          => 1,
            'title'       => 'Lorem ipsum',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit,
            sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'kabkota_id'  => 22,
            'kec_id'      => 446,
            'kel_id'      => 6082,
            'status'      => 7,
            'category_id' => 9,
            'author_id'   => 36,
        ]);

        $I->amStaff();

        $data = [
            'status' => 10,
            'approval_note' => 'Lorem ipsum',
        ];

        $I->sendPUT('/v1/aspirasi/1', $data);
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);
    }

    public function postStaffProvCanUnpublishedTest(ApiTester $I)
    {
        $I->haveInDatabase('aspirasi', [
            'id'          => 1,
            'title'       => 'Lorem ipsum',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit,
            sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'kabkota_id'  => 22,
            'kec_id'      => 446,
            'kel_id'      => 6082,
            'status'      => 10,
            'category_id' => 9,
            'author_id'   => 36,
        ]);

        $I->amStaff('staffprov');

        $data = [
            'status' => 7, //unpublished
        ];

        $I->sendPUT('/v1/aspirasi/1', $data);
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);
    }

    public function postUserCanNotUnpublishedTest(ApiTester $I)
    {
        $I->haveInDatabase('aspirasi', [
            'id'          => 1,
            'title'       => 'Lorem ipsum',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit,
            sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'kabkota_id'  => 22,
            'kec_id'      => 446,
            'kel_id'      => 6082,
            'status'      => 10,
            'category_id' => 9,
            'author_id'   => 36,
        ]);

        $I->amUser('user');

        $data = [
            'status' => 7, //unpublished
        ];

        $I->sendPUT('/v1/aspirasi/1', $data);
        $I->canSeeResponseCodeIs(403);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => false,
            'status'  => 403,
        ]);
    }

    public function userCanUpdateIfStatusDraft(ApiTester $I)
    {
        $I->haveInDatabase('aspirasi', [
            'id'          => 1,
            'title'       => 'Lorem ipsum',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit,
            sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'kabkota_id'  => 22,
            'kec_id'      => 446,
            'kel_id'      => 6082,
            'status'      => 0,
            'category_id' => 9,
            'author_id'   => 36,
        ]);

        $I->amUser('user');

        $data = [
            'title' => 'Lorem ipsum',
        ];

        $I->sendPUT('/v1/aspirasi/1', $data);
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);
    }

    public function userCanUpdateIfStatusRejected(ApiTester $I)
    {
        $I->haveInDatabase('aspirasi', [
            'id'          => 1,
            'title'       => 'Lorem ipsum',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit,
            sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'kabkota_id'  => 22,
            'kec_id'      => 446,
            'kel_id'      => 6082,
            'status'      => 3,
            'category_id' => 9,
            'author_id'   => 36,
        ]);

        $I->amUser('user');

        $data = [
            'title' => 'Lorem ipsum',
            'status' => 1,
        ];

        $I->sendPUT('/v1/aspirasi/1', $data);
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);
    }

    public function userCannotUpdateIfStatusPending(ApiTester $I)
    {
        $I->haveInDatabase('aspirasi', [
            'id'          => 1,
            'title'       => 'Lorem ipsum',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit,
            sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'kabkota_id'  => 22,
            'kec_id'      => 446,
            'kel_id'      => 6082,
            'status'      => 5,
            'category_id' => 9,
            'author_id'   => 36,
        ]);

        $I->amUser('user');

        $data = [
            'title' => 'Lorem ipsum',
        ];

        $I->sendPUT('/v1/aspirasi/1', $data);
        $I->canSeeResponseCodeIs(403);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => false,
            'status'  => 403,
        ]);
    }

    public function userCannotUpdateIfStatusPublished(ApiTester $I)
    {
        $I->haveInDatabase('aspirasi', [
            'id'          => 1,
            'title'       => 'Lorem ipsum',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit,
            sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'kabkota_id'  => 22,
            'kec_id'      => 446,
            'kel_id'      => 6082,
            'status'      => 10,
            'category_id' => 9,
            'author_id'   => 36,
        ]);

        $I->amUser('user');

        $data = [
            'title' => 'Lorem ipsum',
        ];

        $I->sendPUT('/v1/aspirasi/1', $data);
        $I->canSeeResponseCodeIs(403);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => false,
            'status'  => 403,
        ]);
    }

    public function userCanDeleteIfDraftTest(ApiTester $I)
    {
        $I->haveInDatabase('aspirasi', [
            'id'          => 1,
            'title'       => 'Lorem ipsum',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit,
            sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'kabkota_id'  => 22,
            'kec_id'      => 446,
            'kel_id'      => 6082,
            'status'      => 0,
            'category_id' => 9,
            'author_id'   => 36,
        ]);

        $I->amUser('user');

        $I->sendDELETE('/v1/aspirasi/1');
        $I->canSeeResponseCodeIs(204);
    }

    public function userCanDeleteIfRejectedTest(ApiTester $I)
    {
        $I->haveInDatabase('aspirasi', [
            'id'          => 1,
            'title'       => 'Lorem ipsum',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit,
            sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'kabkota_id'  => 22,
            'kec_id'      => 446,
            'kel_id'      => 6082,
            'status'      => 3,
            'category_id' => 9,
            'author_id'   => 36,
        ]);

        $I->amUser('user');

        $I->sendDELETE('/v1/aspirasi/1');
        $I->canSeeResponseCodeIs(204);
    }

    public function userCannotDeleteIfPendingTest(ApiTester $I)
    {
        $I->haveInDatabase('aspirasi', [
            'id'          => 1,
            'title'       => 'Lorem ipsum',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit,
            sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'kabkota_id'  => 22,
            'kec_id'      => 446,
            'kel_id'      => 6082,
            'status'      => 5,
            'category_id' => 9,
            'author_id'   => 36,
        ]);

        $I->amUser('user');

        $I->sendDELETE('/v1/aspirasi/1');
        $I->canSeeResponseCodeIs(403);
    }

    public function userCannotDeleteIfPublishedTest(ApiTester $I)
    {
        $I->haveInDatabase('aspirasi', [
            'id'          => 1,
            'title'       => 'Lorem ipsum',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit,
            sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'kabkota_id'  => 22,
            'kec_id'      => 446,
            'kel_id'      => 6082,
            'status'      => 10,
            'category_id' => 9,
            'author_id'   => 36,
        ]);

        $I->amUser('user');

        $I->sendDELETE('/v1/aspirasi/1');
        $I->canSeeResponseCodeIs(403);
    }

    public function userCannotDeleteIfUnauthorizedTest(ApiTester $I)
    {
        $I->haveInDatabase('aspirasi', [
            'id'          => 1,
            'title'       => 'Lorem ipsum',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit,
            sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'kabkota_id'  => 22,
            'kec_id'      => 446,
            'kel_id'      => 6082,
            'status'      => 10,
            'category_id' => 9,
            'author_id'   => 1,
        ]);

        $I->amUser('user');

        $I->sendDELETE('/v1/aspirasi/1');
        $I->canSeeResponseCodeIs(403);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => false,
            'status'  => 403,
        ]);
    }

    // My User
    public function getMyListTest(ApiTester $I)
    {
        $I->amUser('user');

        $I->sendGET('/v1/aspirasi/me');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);
    }

    public function postLikeAspirasi(ApiTester $I)
    {
        $I->haveInDatabase('aspirasi', [
            'id'          => 1,
            'title'       => 'Lorem ipsum',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit,
            sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'kabkota_id'  => 22,
            'kec_id'      => 446,
            'kel_id'      => 6082,
            'status'      => 10,
            'category_id' => 9,
            'author_id'   => 36,
        ]);

        $I->amUser('user');

        $I->sendPOST('/v1/aspirasi/likes/1');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeInDatabase('aspirasi_likes', ['user_id' => 36, 'aspirasi_id' => 1]);
    }

    public function postDislikeAspirasi(ApiTester $I)
    {
        $I->haveInDatabase('aspirasi', [
            'id'          => 1,
            'title'       => 'Lorem ipsum',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit,
            sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'kabkota_id'  => 22,
            'kec_id'      => 446,
            'kel_id'      => 6082,
            'status'      => 10,
            'category_id' => 9,
            'author_id'   => 36,
        ]);

        $I->haveInDatabase('aspirasi_likes', [
            'aspirasi_id' => 1,
            'user_id'     => 36,
        ]);

        $I->amUser('user');

        $I->sendPOST('/v1/aspirasi/likes/1');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->dontSeeInDatabase('aspirasi_likes', ['user_id' => 36, 'aspirasi_id' => 1]);
    }

    public function staffKabkotaKecKelApproveUnauthorizedTest(ApiTester $I)
    {
        $I->haveInDatabase('aspirasi', [
            'id'          => 1,
            'title'       => 'title',
            'description' => 'description',
            'kabkota_id'  => 22,
            'kec_id'      => 431,
            'kel_id'      => 6093,
            'status'      => 5,
            'category_id' => 9,
            'author_id'   => 1,
            'created_at'  => 1,
        ]);

        $I->amStaff('staffkabkota');
        $I->sendPOST('/v1/aspirasi/approval/1');
        $I->canSeeResponseCodeIs(403);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => false,
            'status'  => 403,
        ]);

        $I->amStaff('staffkec');
        $I->sendPOST('/v1/aspirasi/approval/1');
        $I->canSeeResponseCodeIs(403);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => false,
            'status'  => 403,
        ]);

        $I->amStaff('staffkel');
        $I->sendPOST('/v1/aspirasi/approval/1');
        $I->canSeeResponseCodeIs(403);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => false,
            'status'  => 403,
        ]);
    }

    public function staffProvRejectTest(ApiTester $I)
    {
        $I->haveInDatabase('aspirasi', [
            'id'          => 1,
            'title'       => 'title',
            'description' => 'description',
            'kabkota_id'  => 22,
            'kec_id'      => 431,
            'kel_id'      => 6093,
            'status'      => Aspirasi::STATUS_APPROVAL_PENDING,
            'category_id' => 9,
            'author_id'   => 1,
            'created_at'  => 1,
        ]);

        $data = [
            'action' => Aspirasi::ACTION_REJECT,
            'note'   => 'note',
        ];

        $I->amStaff('staffprov');
        $I->sendPOST('/v1/aspirasi/approval/1', $data);
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeInDatabase('aspirasi', [
            'id'            => 1,
            'status'        => Aspirasi::STATUS_APPROVAL_REJECTED,
            'approved_by'   => 2,
            'approval_note' => 'note',
        ]);
    }

    public function staffProvPublishTest(ApiTester $I)
    {
        $I->haveInDatabase('aspirasi', [
            'id'          => 1,
            'title'       => 'title',
            'description' => 'description',
            'kabkota_id'  => 22,
            'kec_id'      => 431,
            'kel_id'      => 6093,
            'status'      => Aspirasi::STATUS_APPROVAL_PENDING,
            'category_id' => 9,
            'author_id'   => 1,
            'created_at'  => 1,
        ]);

        $data = [
            'action' => Aspirasi::ACTION_APPROVE,
            'note'   => 'note',
        ];

        $I->amStaff('staffprov');
        $I->sendPOST('/v1/aspirasi/approval/1', $data);
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeInDatabase('aspirasi', [
            'id'            => 1,
            'status'        => Aspirasi::STATUS_PUBLISHED,
            'approved_by'   => 2,
            'approval_note' => 'note',
        ]);
    }

    public function getStaffList(ApiTester $I)
    {
        Yii::$app->db->createCommand()->checkIntegrity(false)->execute();

        Yii::$app->db->createCommand('TRUNCATE aspirasi_likes')->execute();
        Yii::$app->db->createCommand('TRUNCATE aspirasi')->execute();

        $I->haveInDatabase('aspirasi', [
            'id'          => 1,
            'title'       => 'kabkota_id 22, kec_id 431, kel_id 6093',
            'description' => 'description',
            'kabkota_id'  => 22,
            'kec_id'      => 431,
            'kel_id'      => 6093,
            'status'      => 10,
            'category_id' => 9,
            'author_id'   => 1,
            'created_at'  => 1,
        ]);

        $I->haveInDatabase('aspirasi', [
            'id'          => 2,
            'title'       => 'kabkota_id 23, kec_id 450, kel_id 6214',
            'description' => 'description',
            'kabkota_id'  => 23,
            'kec_id'      => 450,
            'kel_id'      => 6214,
            'status'      => 10,
            'category_id' => 9,
            'author_id'   => 1,
            'created_at'  => 2,
        ]);

        $I->haveInDatabase('aspirasi', [
            'id'          => 3,
            'title'       => 'kabkota_id 22, kec_id 432, kel_id 6101',
            'description' => 'description',
            'kabkota_id'  => 22,
            'kec_id'      => 432,
            'kel_id'      => 6101,
            'status'      => 10,
            'category_id' => 9,
            'author_id'   => 1,
            'created_at'  => 3,
        ]);

        $I->haveInDatabase('aspirasi', [
            'id'          => 4,
            'title'       => 'kabkota_id 22, kec_id 431, kel_id 6094',
            'description' => 'description',
            'kabkota_id'  => 22,
            'kec_id'      => 431,
            'kel_id'      => 6094,
            'status'      => 10,
            'category_id' => 9,
            'author_id'   => 1,
            'created_at'  => 4,
        ]);

        // Login as Staff Provinsi
        $I->amStaff('staffprov');

        $I->sendGET('/v1/aspirasi');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $data = $I->grabDataFromResponseByJsonPath('$.data.items');

        $I->assertEquals(4, count($data[0]));
        $I->assertEquals(4, $data[0][0]['id']);
        $I->assertEquals(3, $data[0][1]['id']);

        // Login as Staff Kab/Kota
        $I->amStaff('staffkabkota');

        $I->sendGET('/v1/aspirasi');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $data = $I->grabDataFromResponseByJsonPath('$.data.items');

        $I->assertEquals(3, count($data[0]));
        $I->assertEquals(3, $data[0][1]['id']);
        $I->assertEquals(1, $data[0][2]['id']);

        // Login as Staff Kecamatan
        $I->amStaff('staffkec');

        $I->sendGET('/v1/aspirasi');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $data = $I->grabDataFromResponseByJsonPath('$.data.items');

        $I->assertEquals(2, count($data[0]));
        $I->assertEquals(4, $data[0][0]['id']);
        $I->assertEquals(1, $data[0][1]['id']);

        // Login as Staff Kelurahan
        $I->amStaff('staffkel');

        $I->sendGET('/v1/aspirasi');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $data = $I->grabDataFromResponseByJsonPath('$.data.items');

        $I->assertEquals(1, count($data[0]));
        $I->assertEquals(1, $data[0][0]['id']);
    }
}
