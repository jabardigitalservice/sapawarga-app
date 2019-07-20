<?php

class BroadcastListCest
{
    public function _before(ApiTester $I)
    {
        Yii::$app->db->createCommand()->checkIntegrity(false)->execute();

        Yii::$app->db->createCommand('TRUNCATE broadcasts')->execute();

        $this->insertTestData($I);
    }

    protected function insertTestData(ApiTester $I)
    {
        $I->haveInDatabase('broadcasts', [
            'id'          => 1,
            'category_id' => 5,
            'author_id'   => 1,
            'title'       => 'Lorem.',
            'description' => 'Lorem ipsum.',
            'kabkota_id'  => null,
            'kec_id'      => null,
            'kel_id'      => null,
            'status'      => 10,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->haveInDatabase('broadcasts', [
            'id'          => 2,
            'category_id' => 5,
            'author_id'   => 1,
            'title'       => 'Lorem.',
            'description' => 'Lorem ipsum.',
            'kabkota_id'  => 22,
            'kec_id'      => null,
            'kel_id'      => null,
            'status'      => 10,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->haveInDatabase('broadcasts', [
            'id'          => 3,
            'category_id' => 5,
            'author_id'   => 1,
            'title'       => 'Lorem.',
            'description' => 'Lorem ipsum.',
            'kabkota_id'  => 23,
            'kec_id'      => null,
            'kel_id'      => null,
            'status'      => 10,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->haveInDatabase('broadcasts', [
            'id'          => 4,
            'category_id' => 5,
            'author_id'   => 1,
            'title'       => 'Lorem.',
            'description' => 'Lorem ipsum.',
            'kabkota_id'  => 22,
            'kec_id'      => 431,
            'kel_id'      => null,
            'status'      => 10,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->haveInDatabase('broadcasts', [
            'id'          => 5,
            'category_id' => 5,
            'author_id'   => 1,
            'title'       => 'Lorem.',
            'description' => 'Lorem ipsum.',
            'kabkota_id'  => 22,
            'kec_id'      => 432,
            'kel_id'      => null,
            'status'      => 10,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->haveInDatabase('broadcasts', [
            'id'          => 6,
            'category_id' => 5,
            'author_id'   => 1,
            'title'       => 'Lorem.',
            'description' => 'Lorem ipsum.',
            'kabkota_id'  => 22,
            'kec_id'      => 431,
            'kel_id'      => 6093,
            'status'      => 10,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);

        $I->haveInDatabase('broadcasts', [
            'id'          => 7,
            'category_id' => 5,
            'author_id'   => 1,
            'title'       => 'Lorem.',
            'description' => 'Lorem ipsum.',
            'kabkota_id'  => 22,
            'kec_id'      => 431,
            'kel_id'      => 6094,
            'status'      => 10,
            'created_at'  => '1554706345',
            'updated_at'  => '1554706345',
        ]);
    }

    public function getBroadcastStaffProvList(ApiTester $I)
    {
        $I->amStaff('staffprov');

        $I->sendGET('/v1/broadcasts');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeHttpHeader('X-Pagination-Total-Count', 7);
        $data = $I->grabDataFromResponseByJsonPath('$.data.items');

        $I->assertEquals(1, $data[0][0]['id']);
        $I->assertEquals(2, $data[0][1]['id']);
        $I->assertEquals(3, $data[0][2]['id']);
        $I->assertEquals(4, $data[0][3]['id']);
        $I->assertEquals(5, $data[0][4]['id']);
        $I->assertEquals(6, $data[0][5]['id']);
        $I->assertEquals(7, $data[0][6]['id']);

        // Filter & Search

        $I->sendGET('/v1/broadcasts?kabkota_id=22');
        $I->seeHttpHeader('X-Pagination-Total-Count', 6);
        $data = $I->grabDataFromResponseByJsonPath('$.data.items');

        $I->assertEquals(1, $data[0][0]['id']);
        $I->assertEquals(2, $data[0][1]['id']);
        $I->assertEquals(4, $data[0][2]['id']);
        $I->assertEquals(5, $data[0][3]['id']);
        $I->assertEquals(6, $data[0][4]['id']);
        $I->assertEquals(7, $data[0][5]['id']);

        // ----
        $I->sendGET('/v1/broadcasts?kabkota_id=23');
        $I->seeHttpHeader('X-Pagination-Total-Count', 2);
        $data = $I->grabDataFromResponseByJsonPath('$.data.items');

        $I->assertEquals(1, $data[0][0]['id']);
        $I->assertEquals(3, $data[0][1]['id']);

        // ----
        $I->sendGET('/v1/broadcasts?kabkota_id=22&kec_id=431');
        $I->seeHttpHeader('X-Pagination-Total-Count', 5);
        $data = $I->grabDataFromResponseByJsonPath('$.data.items');

        $I->assertEquals(1, $data[0][0]['id']);
        $I->assertEquals(2, $data[0][1]['id']);
        $I->assertEquals(4, $data[0][2]['id']);
        $I->assertEquals(6, $data[0][3]['id']);
        $I->assertEquals(7, $data[0][4]['id']);

        // ----
        $I->sendGET('/v1/broadcasts?kabkota_id=22&kec_id=432');
        $I->seeHttpHeader('X-Pagination-Total-Count', 3);
        $data = $I->grabDataFromResponseByJsonPath('$.data.items');

        $I->assertEquals(1, $data[0][0]['id']);
        $I->assertEquals(2, $data[0][1]['id']);
        $I->assertEquals(5, $data[0][2]['id']);

        // ----
        $I->sendGET('/v1/broadcasts?kabkota_id=22&kec_id=431&kel_id=6093');
        $I->seeHttpHeader('X-Pagination-Total-Count', 4);
        $data = $I->grabDataFromResponseByJsonPath('$.data.items');

        $I->assertEquals(1, $data[0][0]['id']);
        $I->assertEquals(2, $data[0][1]['id']);
        $I->assertEquals(4, $data[0][2]['id']);
        $I->assertEquals(6, $data[0][3]['id']);

        // ----
        $I->sendGET('/v1/broadcasts?kabkota_id=22&kec_id=431&kel_id=6094');
        $I->seeHttpHeader('X-Pagination-Total-Count', 4);
        $data = $I->grabDataFromResponseByJsonPath('$.data.items');

        $I->assertEquals(1, $data[0][0]['id']);
        $I->assertEquals(2, $data[0][1]['id']);
        $I->assertEquals(4, $data[0][2]['id']);
        $I->assertEquals(7, $data[0][3]['id']);
    }

    public function getBroadcastStaffKabkotaList(ApiTester $I)
    {
        $I->amStaff('staffkabkota');

        $I->sendGET('/v1/broadcasts');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeHttpHeader('X-Pagination-Total-Count', 6);
        $data = $I->grabDataFromResponseByJsonPath('$.data.items');

        $I->assertEquals(1, $data[0][0]['id']);
        $I->assertEquals(2, $data[0][1]['id']);
        $I->assertEquals(4, $data[0][2]['id']);
        $I->assertEquals(5, $data[0][3]['id']);
        $I->assertEquals(6, $data[0][4]['id']);
        $I->assertEquals(7, $data[0][5]['id']);

        // Filter & Search
    }

    public function getBroadcastStaffKecamatanList(ApiTester $I)
    {
        $I->amStaff('staffkec');

        $I->sendGET('/v1/broadcasts');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeHttpHeader('X-Pagination-Total-Count', 5);
        $data = $I->grabDataFromResponseByJsonPath('$.data.items');

        $I->assertEquals(1, $data[0][0]['id']);
        $I->assertEquals(2, $data[0][1]['id']);
        $I->assertEquals(4, $data[0][2]['id']);
        $I->assertEquals(6, $data[0][3]['id']);
        $I->assertEquals(7, $data[0][4]['id']);

        // Filter & Search
    }

    public function getBroadcastStaffKelurahanList(ApiTester $I)
    {
        $I->amStaff('staffkel');

        $I->sendGET('/v1/broadcasts');
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
        $I->assertEquals(4, $data[0][2]['id']);
        $I->assertEquals(6, $data[0][3]['id']);

        // Filter & Search
    }
}
