<?php

class DashboardCest
{

    public function _before(ApiTester $I)
    {
        Yii::$app->db->createCommand()->checkIntegrity(false)->execute();

        // polling
        Yii::$app->db->createCommand('TRUNCATE polling_votes')->execute();
        Yii::$app->db->createCommand('TRUNCATE polling_answers')->execute();
        Yii::$app->db->createCommand('TRUNCATE polling')->execute();

        // aspirasi
        Yii::$app->db->createCommand('TRUNCATE aspirasi')->execute();
        Yii::$app->db->createCommand('TRUNCATE aspirasi_likes')->execute();

        $aspirasiData = file_get_contents(__DIR__ . '/../data/dasboard-aspirasi-sample.sql');

        Yii::$app->db->createCommand($aspirasiData)->execute();
    }

    public function getAccessAspirasiMostLikeAdminTest(ApiTester $I)
    {
        $I->amStaff('admin');

        $I->sendGET('/v1/dashboards/aspirasi-most-likes');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);
    }

    public function getAccessAspirasiMostLikeStaffProvTest(ApiTester $I)
    {
        $I->amStaff('staffprov');

        $I->sendGET('/v1/dashboards/aspirasi-most-likes');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);
    }

    public function getAccessAspirasiMostLikeStaffKecFailTest(ApiTester $I)
    {
        $I->amStaff('staffkec');

        $I->sendGET('/v1/dashboards/aspirasi-most-likes');
        $I->canSeeResponseCodeIs(403);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => false,
            'status'  => 403,
        ]);
    }
    public function getAccessAspirasiMostLikeStaffKelFailTest(ApiTester $I)
    {
        $I->amStaff('staffkel');

        $I->sendGET('/v1/dashboards/aspirasi-most-likes');
        $I->canSeeResponseCodeIs(403);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => false,
            'status'  => 403,
        ]);
    }

    public function getAccessAspirasiMostLikeUserFailTest(ApiTester $I)
    {
        $I->amUser();

        $I->sendGET('/v1/dashboards/aspirasi-most-likes');
        $I->canSeeResponseCodeIs(403);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => false,
            'status'  => 403,
        ]);
    }

    public function getAspirasiMostLikeTest(ApiTester $I)
    {
        $I->amStaff('staffprov');

        $I->sendGET('/v1/dashboards/aspirasi-most-likes');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $data = $I->grabDataFromResponseByJsonPath('$.data.items');

        $I->assertEquals(4, $data[0][0]['total_likes']);
        $I->assertEquals(3, $data[0][1]['total_likes']);
        $I->assertEquals(2, $data[0][2]['total_likes']);
    }

    public function getFilterCategoryAspirasiMostLikeTest(ApiTester $I)
    {
        $I->amStaff('staffprov');

        $I->sendGET('/v1/dashboards/aspirasi-most-likes?category_id=9');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeResponseContainsJson([
            'category_id' => 9,
        ]);

        $I->dontSeeResponseContainsJson([
            'category_id' => 10,
        ]);

        $data = $I->grabDataFromResponseByJsonPath('$.data.items');
    }

    public function getFilterKabKotaAspirasiMostLikeTest(ApiTester $I)
    {
        $I->amStaff('staffprov');

        $I->sendGET('/v1/dashboards/aspirasi-most-likes?kabkota_id=22');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeResponseContainsJson([
            'kabkota_id' => 22,
        ]);

        $I->dontSeeResponseContainsJson([
            'kabkota_id' => 23,
        ]);
    }

    public function getAspirasiCountByStatusTest(ApiTester $I)
    {
        $I->amStaff('staffprov');

        $I->sendGET('/v1/dashboards/aspirasi-counts');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $data = $I->grabDataFromResponseByJsonPath('$.data');

        $I->assertEquals(1, $data[0]['STATUS_APPROVAL_PENDING']);
        $I->assertEquals(4, $data[0]['STATUS_PUBLISHED']);
    }

    public function getAspirasiCountByWilayahTest(ApiTester $I)
    {
        $I->amStaff('staffprov');

        $I->sendGET('/v1/dashboards/aspirasi-geo');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $data = $I->grabDataFromResponseByJsonPath('$.data');

        $I->assertEquals('KOTA BANDUNG', $data[0][0]['name']);
        $I->assertEquals(3, $data[0][0]['counts']);
        $I->assertEquals(22, $data[0][0]['id']);

        $I->assertEquals('KOTA BEKASI', $data[0][1]['name']);
        $I->assertEquals(1, $data[0][1]['counts']);
        $I->assertEquals(23, $data[0][1]['id']);
    }

    public function getAspirasiCountByKecBandungTest(ApiTester $I)
    {
        $I->amStaff('staffprov');

        $I->sendGET('/v1/dashboards/aspirasi-geo?kabkota_id=22');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $data = $I->grabDataFromResponseByJsonPath('$.data');

        $I->assertEquals('BANDUNG WETAN', $data[0][0]['name']);
        $I->assertEquals(2, $data[0][0]['counts']);
        $I->assertEquals(446, $data[0][0]['id']);
    }

    public function getAspirasiCountByKelBandungTest(ApiTester $I)
    {
        $I->amStaff('staffprov');

        $I->sendGET('/v1/dashboards/aspirasi-geo?kec_id=446');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $data = $I->grabDataFromResponseByJsonPath('$.data');

        $I->assertEquals('TAMANSARI', $data[0][0]['name']);
        $I->assertEquals(2, $data[0][0]['counts']);
        $I->assertEquals(6178, $data[0][0]['id']);
    }


    public function getPollingChartTest(ApiTester $I)
    {
        $I->haveInDatabase('polling', [
            'name'        => 'Lorem Ipsum Dolor Sit Amet',
            'description' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
            'excerpt'     => 'Lorem ipsum dolor sit amet',
            'start_date'  => '2019-06-01',
            'end_date'    => '2019-09-01',
            'kabkota_id'  => 22,
            'kec_id'      => 446,
            'kel_id'      => 6082,
            'status'      => 0,
            'category_id' => 17,
            'created_by'  => 1,
            'updated_by'  => 1,
        ]);

        $I->haveInDatabase('polling_answers', [
            'polling_id' => 1,
            'body' => 'Option A',
        ]);

        $I->haveInDatabase('polling_answers', [
            'polling_id' => 1,
            'body' => 'Option B',
        ]);

        $I->haveInDatabase('polling_answers', [
            'polling_id' => 1,
            'body' => 'Option C',
        ]);

        $I->haveInDatabase('polling_votes', [
            'id'         => 1,
            'polling_id' => 1,
            'answer_id'  => 1,
            'user_id'    => 36,
            'created_at' => '1554706345',
            'updated_at' => '1554706345',
        ]);

        $I->haveInDatabase('polling_votes', [
            'id'         => 2,
            'polling_id' => 1,
            'answer_id'  => 1,
            'user_id'    => 35,
            'created_at' => '1554706345',
            'updated_at' => '1554706345',
        ]);

        $I->haveInDatabase('polling_votes', [
            'id'         => 3,
            'polling_id' => 1,
            'answer_id'  => 2,
            'user_id'    => 34,
            'created_at' => '1554706345',
            'updated_at' => '1554706345',
        ]);

        $I->amStaff('staffprov');

        $I->sendGET('/v1/dashboards/polling-chart?id=1');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $data = $I->grabDataFromResponseByJsonPath('$.data');

        $I->assertEquals('Option A', $data[0][0]['body']);
        $I->assertEquals(2, $data[0][0]['votes']);
        $I->assertEquals(66.67, $data[0][0]['percentage']);

        $I->assertEquals('Option B', $data[0][1]['body']);
        $I->assertEquals(1, $data[0][1]['votes']);
        $I->assertEquals(33.33, $data[0][1]['percentage']);

        $I->assertEquals('Option C', $data[0][2]['body']);
        $I->assertEquals(0, $data[0][2]['votes']);
        $I->assertEquals(0.00, $data[0][2]['percentage']);
    }

    public function _after(ApiTester $I)
    {
        Yii::$app->db->createCommand()->checkIntegrity(false)->execute();

        // aspirasi
        Yii::$app->db->createCommand('TRUNCATE aspirasi')->execute();
        Yii::$app->db->createCommand('TRUNCATE aspirasi_likes')->execute();

        // polling
        Yii::$app->db->createCommand('TRUNCATE polling_votes')->execute();
        Yii::$app->db->createCommand('TRUNCATE polling_answers')->execute();
        Yii::$app->db->createCommand('TRUNCATE polling')->execute();

    }
}
