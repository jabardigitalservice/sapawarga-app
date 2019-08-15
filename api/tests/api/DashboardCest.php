<?php

class DashboardCest
{

    public function getAccessTopUsulanAdminTest(ApiTester $I)
    {
        $I->amStaff('admin');

        $I->sendGET('/v1/dashboards/usulantop');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);
    }

    public function getAccessTopUsulanStaffProvTest(ApiTester $I)
    {
        $I->amStaff('staffprov');

        $I->sendGET('/v1/dashboards/usulantop');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);
    }

    public function getAccessTopUsulanStaffKecFailTest(ApiTester $I)
    {
        $I->amStaff('staffkec');

        $I->sendGET('/v1/dashboards/usulantop');
        $I->canSeeResponseCodeIs(403);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => false,
            'status'  => 403,
        ]);
    }
    public function getAccessTopUsulanStaffKelFailTest(ApiTester $I)
    {
        $I->amStaff('staffkel');

        $I->sendGET('/v1/dashboards/usulantop');
        $I->canSeeResponseCodeIs(403);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => false,
            'status'  => 403,
        ]);
    }

    public function getAccessTopUsulanUserFailTest(ApiTester $I)
    {
        $I->amUser();

        $I->sendGET('/v1/dashboards/usulantop');
        $I->canSeeResponseCodeIs(403);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => false,
            'status'  => 403,
        ]);
    }
}
