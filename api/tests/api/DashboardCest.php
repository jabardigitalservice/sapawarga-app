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

    public function _after(ApiTester $I)
    {
        Yii::$app->db->createCommand()->checkIntegrity(false)->execute();

        Yii::$app->db->createCommand('TRUNCATE aspirasi')->execute();
        Yii::$app->db->createCommand('TRUNCATE aspirasi_likes')->execute();
    }
}
