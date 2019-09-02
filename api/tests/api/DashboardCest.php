<?php

class DashboardCest
{

    public function _before(ApiTester $I)
    {
        Yii::$app->db->createCommand()->checkIntegrity(false)->execute();

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
        $I->assertEquals('-6.95981961897412', $data[0][0]['latitude']);
        $I->assertEquals('107.590417459601', $data[0][0]['longitude']);

        $I->assertEquals('KOTA BEKASI', $data[0][1]['name']);
        $I->assertEquals(1, $data[0][1]['counts']);
        $I->assertEquals(23, $data[0][1]['id']);
        $I->assertEquals('-6.29371311907745', $data[0][1]['latitude']);
        $I->assertEquals('106.922564116874', $data[0][1]['longitude']);
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
        $I->assertEquals('-6.90150426495919', $data[0][0]['latitude']);
        $I->assertEquals('107.607289221673', $data[0][0]['longitude']);
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
        $I->assertEquals('-6.90150426495919', $data[0][0]['latitude']);
        $I->assertEquals('107.607289221673', $data[0][0]['longitude']);
    }

    public function _after(ApiTester $I)
    {
        Yii::$app->db->createCommand()->checkIntegrity(false)->execute();

        Yii::$app->db->createCommand('TRUNCATE aspirasi')->execute();
        Yii::$app->db->createCommand('TRUNCATE aspirasi_likes')->execute();
    }
}
