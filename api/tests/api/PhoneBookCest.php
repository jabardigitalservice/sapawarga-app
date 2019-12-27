<?php

class PhoneBookCest
{
    public function _before(ApiTester $I)
    {
        //
    }

    public function getListBandungTest(ApiTester $I)
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

    public function getListBekasiTest(ApiTester $I)
    {
        $I->amUser('user.bekasi');

        $I->sendGET('/v1/phone-books');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeResponseContainsJson([
            'kabkota_id' => 23,
        ]);

        $I->cantSeeResponseContainsJson([
            'kabkota_id' => 22,
        ]);

        $I->cantSeeResponseContainsJson([
            'kabkota_id' => 26,
        ]);
    }

    public function getListTasikmalayaTest(ApiTester $I)
    {
        $I->amUser('user.tasik');

        $I->sendGET('/v1/phone-books');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeResponseContainsJson([
            'kabkota_id' => 26,
        ]);

        $I->cantSeeResponseContainsJson([
            'kabkota_id' => 22,
        ]);

        $I->cantSeeResponseContainsJson([
            'kabkota_id' => 23,
        ]);
    }

    public function getListCallCenterTest(ApiTester $I)
    {
        $I->amUser('user.bandung');

        $I->sendGET('/v1/phone-books');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status' => 200,
        ]);

        $I->seeResponseContainsJson([
            'kabkota_id' => null,
            'kec_id'     => null,
            'kel_id'     => null,
        ]);
    }

    public function getListFilterBandungTest(ApiTester $I)
    {
        $I->amUser('user.bandung');

        $I->sendGET('/v1/phone-books?kabkota_id=22');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->cantSeeResponseContainsJson([
            'kabkota_id' => 23,
        ]);

        $I->cantSeeResponseContainsJson([
            'kabkota_id' => 26,
        ]);
    }

    public function getListFilterBekasiTest(ApiTester $I)
    {
        $I->amUser('user.bandung');

        $I->sendGET('/v1/phone-books?kabkota_id=23');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
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

    public function getListStaffKabKotaBekasiTest(ApiTester $I)
    {
        $I->amStaff('staffkabkota2');

        $I->sendGET('/v1/phone-books');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeResponseContainsJson([
            'kabkota_id' => 23,
        ]);

        $I->cantSeeResponseContainsJson([
            'kabkota_id' => 22,
        ]);

        $I->cantSeeResponseContainsJson([
            'kabkota_id' => 26,
        ]);
    }

   public function getSearchStaffKabKotaBekasiOverideKabKotaIdTest(ApiTester $I)
   {
       $I->amStaff('staffkabkota2');
       $I->sendGET('/v1/phone-books?search=koperasi&kabkota_id=22');
       $I->canSeeResponseCodeIs(200);
       $I->seeResponseIsJson();

       $response = $I->grabResponse();
       $response = json_decode($response, true);
       $response = $response['data']['items'];
       $kabkotaColumn = array_column($response, 'kabkota_id');

       // Assert kabkota_id values in search result
       $expectedSearch = array_search(23, $kabkotaColumn);
       $I->assertNotFalse($expectedSearch);
       $I->assertInternalType(int::class, $expectedSearch);

       $unexpectedSearch = array_search(22, $kabkotaColumn);
       $I->assertFalse($unexpectedSearch);

       $unexpectedSearch = array_search(26, $kabkotaColumn);
       $I->assertFalse($unexpectedSearch);
   }

   public function getPolresByUserLocation(ApiTester $I)
   {
       $I->amUser('user.tasik');
       $I->sendGET('/v1/phone-books/by-user-location?instansi=polres');
       $I->canSeeResponseCodeIs(200);
       $I->seeResponseIsJson();

       $I->seeResponseContainsJson([
           'success' => true,
           'status'  => 200,
           'data'    => [ 'kabkota_id' => 26, ]
       ]);

       $I->cantSeeResponseContainsJson([
           'name' => 'POLRES TASIK MALAYA KOTA',
       ]);

       $I->cantSeeResponseContainsJson([
           'kabkota_id' => 23,
       ]);
   }

    public function userCannotCreateNewTest(ApiTester $I)
    {
        $I->amUser();

        $I->sendPOST('/v1/phone-books');
        $I->canSeeResponseCodeIs(403);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => false,
            'status'  => 403,
        ]);
    }

    public function userCannotUpdateTest(ApiTester $I)
    {
        $I->amUser();

        $I->sendPUT('/v1/phone-books/1');
        $I->canSeeResponseCodeIs(403);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => false,
            'status'  => 403,
        ]);
    }

    public function userCannotDeleteTest(ApiTester $I)
    {
        $I->amUser();

        $I->sendDELETE('/v1/phone-books/1');
        $I->canSeeResponseCodeIs(403);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => false,
            'status'  => 403,
        ]);
    }

    public function staffCreateNewTest(ApiTester $I)
    {
        $I->amStaff();

        $I->sendPOST('/v1/phone-books', [
            'name' => 'Test Name',
            'description' => 'Test Description',
            'address' => 'Ini alamat.',
            'category_id' => 1,
            'phone_numbers' => [
                [
                    'phone_number' => '022-1234',
                    'type' => 'phone',
                ],
                [
                    'phone_number' => '022-9876',
                    'type' => 'messaging',
                ]
            ],
            'meta' => null,
            'kabkota_id' => 22,
            'seq' => 1,
            'status' => 10,
        ]);

        $I->canSeeResponseCodeIs(201);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 201,
        ]);
    }

    public function staffUpdateTest(ApiTester $I)
    {
        $I->amStaff();

        $I->sendPUT('/v1/phone-books/1', [
            'name' => 'Test Name',
            'description' => 'Test Description',
            'address' => 'Ini alamat.',
            'category_id' => 1,
            'phone_numbers' => [
                [
                    'phone_number' => '022-1234',
                    'type' => 'phone',
                ],
                [
                    'phone_number' => '022-9876',
                    'type' => 'messaging',
                ]
            ],
            'meta' => null,
            'kabkota_id' => 22,
            'seq' => 1,
            'status' => 10,
        ]);

        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);
    }

    public function staffDelete(ApiTester $I)
    {
        $I->amStaff();

        $I->sendDELETE('/v1/phone-books/1');
        $I->canSeeResponseCodeIs(204);
    }
}
