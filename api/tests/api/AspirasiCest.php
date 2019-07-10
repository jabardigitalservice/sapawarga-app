<?php

use Illuminate\Support\Arr;

class AspirasiCest
{
    public function _before(ApiTester $I)
    {
        //
    }

    public function getListTest(ApiTester $I)
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

    public function getListOrderByCategoryNameAscendingTest(ApiTester $I)
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
            'category_id' => 9, // INFRASTRUKTUR
            'author_id'   => 36,
        ]);

        $I->haveInDatabase('aspirasi', [
            'id'          => 2,
            'title'       => 'Lorem ipsum',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit,
            sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
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
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit,
            sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
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

    public function postCreateTest(ApiTester $I)
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
        $I->canSeeResponseCodeIs(201);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 201,
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
            'status'      => 10,
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

    public function userCanDeleteTest(ApiTester $I)
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
        $I->canSeeResponseCodeIs(204);
    }

    public function userCannotDeleteUnauthorizedTest(ApiTester $I)
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
}
