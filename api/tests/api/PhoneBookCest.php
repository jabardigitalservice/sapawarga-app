<?php

class PhoneBookCest
{
    public function _before(ApiTester $I)
    {
        //
    }

    public function getListTestBandung(ApiTester $I)
    {
        $I->amUser('user.bandung');

        $I->sendGET('/v1/phone-books');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeResponseContainsJson([
            'kabkota_id' => 22,
        ]);

        $I->cantSeeResponseContainsJson([
            'kabkota_id' => 23,
        ]);

        $I->cantSeeResponseContainsJson([
            'kabkota_id' => 26,
        ]);
    }

    public function getListAdminTest(ApiTester $I)
    {
        $I->amStaff();

        $I->sendGET('/v1/phone-books');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);
    }
}
