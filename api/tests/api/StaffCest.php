<?php

class StaffCest
{
    private $endpointStaff = '/v1/staff';
    private $endpointLogin = '/v1/staff/login';

    public function staffLoginInvalidFields(ApiTester $I)
    {
        $I->sendPOST($this->endpointLogin);

        $I->seeResponseCodeIs(422);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => false,
            'status'  => 422,
        ]);

        $I->sendPOST($this->endpointLogin, [
            'LoginForm' => [
                'username' => 'admin',
            ]
        ]);

        $I->seeResponseCodeIs(422);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => false,
            'status'  => 422,
        ]);

        $I->sendPOST($this->endpointLogin, [
            'LoginForm' => [
                'password' => '123456',
            ]
        ]);

        $I->seeResponseCodeIs(422);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => false,
            'status'  => 422,
        ]);
    }

    public function staffLoginInvalidCredentials(ApiTester $I)
    {
        $I->sendPOST($this->endpointLogin, [
            'LoginForm' => [
                'username' => 'admin',
                'password' => '1234567',
            ]
        ]);

        $I->seeResponseCodeIs(422);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => false,
            'status'  => 422,
        ]);
    }

    public function staffLoginInactiveUsername(ApiTester $I)
    {
        $I->sendPOST($this->endpointLogin, [
            'LoginForm' => [
                'username' => 'staff.inactive',
                'password' => '123456',
            ]
        ]);

        $I->seeResponseCodeIs(422);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => false,
            'status' => 422,
            'data' => [
                'status' => []
            ]
        ]);
    }

    public function staffCreateStaffInvalidFields(ApiTester $I)
    {
        $I->amStaff();

        $I->sendPOST($this->endpointStaff);

        $I->seeResponseCodeIs(422);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success'=> false,
            'status'=> 422,
            'data'=> [
                'username' => [],
                'email' => [],
                'role_id' => [],
                'kabkota_id' => [],
                'kec_id' => [],
                'kel_id' => [],
                'rw' => []
            ]
        ]);

        $I->sendPOST($this->endpointStaff, [
            'username' => 'staff.kabkota.2',
            'email' => 'staff.kabkota.2@jabarprov.go.id',
            'password' => '123456',
            'role_id' => 'staffKabkota'
        ]);

        $I->seeResponseCodeIs(422);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success'=> false,
            'status'=> 422,
            'data'=> [
                'kabkota_id' => []
            ]
        ]);
    }

    public function staffCreateStaff(ApiTester $I)
    {
        $I->amStaff();

        $I->sendPOST($this->endpointStaff, [
            'username' => 'staff.prov.1',
            'email' => 'staff.prov.1@jabarprov.go.id',
            'password' => '123456',
            'role_id' => 'staffProv'
        ]);

        $I->canSeeResponseCodeIs(201);
        $I->seeResponseIsJson();
    }

    public function getList(ApiTester $I)
    {
        $I->amStaff();

        $I->sendGET($this->endpointStaff);

        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);
    }

    public function getListInvalidSearch(ApiTester $I)
    {
        $I->amStaff();

        $I->sendGET($this->endpointStaff . '?search=MZG2gnGQkRZ5XOIYqJz94qHMuRM0P41zAKpdFNHVnDoE1fl2tlA');

        $I->canSeeResponseCodeIs(400);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => false,
            'status'  => 400,
            'data' => []
        ]);
    }

    public function getListByName(ApiTester $I)
    {
        $I->amStaff();

        $I->sendGET($this->endpointStaff . '?search=Staff');

        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
            'data' => [
                'items' => [],
            ]
        ]);
    }

    public function getListByPhone(ApiTester $I)
    {
        $I->amStaff();

        $I->sendGET($this->endpointStaff . '?search=080989999');

        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
            'data' => [
                'items' => [],
            ]
        ]);
    }

    public function getListByRole(ApiTester $I)
    {
        $I->amStaff();

        $I->sendGET($this->endpointStaff . '?role_id=staffRW');

        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
            'data' => [
                'items' => [],
            ]
        ]);
    }

    public function getListByArea(ApiTester $I)
    {
        $I->amStaff();

        $I->sendGET($this->endpointStaff . '?kabkota_id=22&kec_id=431&kel_id=6093&rw=1');

        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
            'data' => [
                'items' => [],
            ]
        ]);
    }

//    public function getItemInvalidParam(ApiTester $I)
//    {
//        $I->amStaff();
//
//        $I->sendGET($this->endpointStaff . '/xsA2#');
//        $I->canSeeResponseCodeIs(400); // Bad Request
//        $I->seeResponseIsJson();
//    }

    public function getItemNotFound(ApiTester $I)
    {
        $I->amStaff();

        $I->sendGET($this->endpointStaff . '/' . PHP_INT_MAX);
        $I->canSeeResponseCodeIs(404); // Not Found
        $I->seeResponseIsJson();
    }

    public function getItem(ApiTester $I)
    {
        $I->amStaff();

        $I->sendGET($this->endpointStaff . '/1');
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
            'data' => [],
        ]);
    }

    public function staffUpdateStaffInvalidFields(ApiTester $I)
    {
        $I->amStaff();

        $I->sendPUT($this->endpointStaff . '/1', [
            'username' => '@D3',
            'email' => 'invalid email@example_.com',
            'status' => 1000,
            'role_id' => 'invalidRole'
        ]);
        $I->canSeeResponseCodeIs(422);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => false,
            'status'  => 422,
            'data' => [
                'username' => [],
                'email' => [],
                'status' => [],
                'role_id' => []
            ],
        ]);
    }

    public function staffUpdateStaff(ApiTester $I)
    {
        $I->amStaff();

        $I->sendPUT($this->endpointStaff . '/2', [
            'username' => 'staffprov',
            'name' => 'Name Edited'
        ]);
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
            'data' => [],
        ]);
    }

    public function staffUpdateOwnProfile(ApiTester $I)
    {
        $I->amStaff('staffprov');

        $I->sendPOST($this->endpointStaff . '/me', [
            'UserEditForm' => [
                'name' => 'Name Edited'
            ]
        ]);
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeInDatabase('user', [
            'username' => 'staffprov',
            'name'     => 'Name Edited',
        ]);
    }


    public function staffCanViewUserJobType(ApiTester $I)
    {
        $I->haveInDatabase('user', [
            'id'                => 100,
            'username'          => 'user.test',
            'auth_key'          => 'Tc4cif87I3Sm3PFnRZLZCpaZoaUnTDtj',
            'email'             => 'user@test.com',
            'unconfirmed_email' => 'user@test.com',
            'confirmed_at'      => time(),
            'role'              => 50,
            'job_type_id'       => 1,
            'status'            => 10,
            'created_at'        => time(),
            'updated_at'        => time(),
        ]);

        $I->amStaff('staffprov');

        $I->sendGET('/v1/staff/100');

        $I->canSeeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'success' => true,
            'data' => [
                'job_type_id' => 1,
                'job_type'    => [
                    'id'    => 1,
                    'title' => 'Belum Bekerja',
                ],
            ]
        ]);
    }

    public function staffCanViewUserEducationLevel(ApiTester $I)
    {
        $I->haveInDatabase('user', [
            'id'                 => 100,
            'username'           => 'user.test',
            'password_hash'      => '$2y$13$9Gouh1ZbewVEh4bQIGsifOs8/RWW/7RIs0CAGNd7tapXFm9.WxiXS',
            'email'              => 'user@test.com',
            'unconfirmed_email'  => 'user@test.com',
            'confirmed_at'       => time(),
            'role'               => 50,
            'education_level_id' => 1,
            'status'             => 10,
            'created_at'         => time(),
            'updated_at'         => time(),
        ]);

        $I->amStaff('staffprov');

        $I->sendGET('/v1/staff/100');

        $I->canSeeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'success' => true,
            'data' => [
                'education_level_id' => 1,
                'education_level'    => [
                    'id'    => 1,
                    'title' => 'Tidak Ada',
                ],
            ]
        ]);
    }

    public function canUpdateBirthDate(ApiTester $I)
    {
        $I->haveInDatabase('user', [
            'id'                 => 100,
            'username'           => 'user.test',
            'password_hash'      => '$2y$13$9Gouh1ZbewVEh4bQIGsifOs8/RWW/7RIs0CAGNd7tapXFm9.WxiXS',
            'email'              => 'user@test.com',
            'unconfirmed_email'  => 'user@test.com',
            'confirmed_at'       => time(),
            'role'               => 50,
            'birth_date'         => null,
            'status'             => 10,
            'created_at'         => time(),
            'updated_at'         => time(),
        ]);

        $I->amStaff('staffprov');

        // Update Value
        $I->sendPUT('/v1/staff/100', ['birth_date' => '1988-11-15']);

        $I->canSeeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'success' => true,
            'data' => [
                'birth_date' => '1988-11-15'
            ]
        ]);

        // Update to NULL
        $I->sendPUT('/v1/staff/100', ['birth_date' => null]);

        $I->canSeeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'success' => true,
            'data' => [
                'birth_date' => null
            ]
        ]);

        // Update skip attribute
        $I->sendPUT('/v1/staff/100', []);

        $I->canSeeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'success' => true,
            'data' => [
                'birth_date' => null
            ]
        ]);
    }

    public function canUpdateJobType(ApiTester $I)
    {
        $I->haveInDatabase('user', [
            'id'                 => 100,
            'username'           => 'user.test',
            'password_hash'      => '$2y$13$9Gouh1ZbewVEh4bQIGsifOs8/RWW/7RIs0CAGNd7tapXFm9.WxiXS',
            'email'              => 'user@test.com',
            'unconfirmed_email'  => 'user@test.com',
            'confirmed_at'       => time(),
            'role'               => 50,
            'job_type_id'        => null,
            'status'             => 10,
            'created_at'         => time(),
            'updated_at'         => time(),
        ]);

        $I->amStaff('staffprov');

        // Update Value
        $I->sendPUT('/v1/staff/100', ['job_type_id' => 1]);

        $I->canSeeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'success' => true,
            'data' => [
                'job_type_id' => 1
            ]
        ]);

        // Update to NULL
        $I->sendPUT('/v1/staff/100', ['job_type_id' => null]);

        $I->canSeeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'success' => true,
            'data' => [
                'job_type_id' => null
            ]
        ]);

        // Update skip attribute
        $I->sendPUT('/v1/staff/100', []);

        $I->canSeeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'success' => true,
            'data' => [
                'job_type_id' => null
            ]
        ]);
    }

    public function canUpdateEducationLevel(ApiTester $I)
    {
        $I->haveInDatabase('user', [
            'id'                 => 100,
            'username'           => 'user.test',
            'password_hash'      => '$2y$13$9Gouh1ZbewVEh4bQIGsifOs8/RWW/7RIs0CAGNd7tapXFm9.WxiXS',
            'email'              => 'user@test.com',
            'unconfirmed_email'  => 'user@test.com',
            'confirmed_at'       => time(),
            'role'               => 50,
            'education_level_id' => null,
            'status'             => 10,
            'created_at'         => time(),
            'updated_at'         => time(),
        ]);

        $I->amStaff('staffprov');

        // Update Value
        $I->sendPUT('/v1/staff/100', ['education_level_id' => 1]);

        $I->canSeeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'success' => true,
            'data' => [
                'education_level_id' => 1
            ]
        ]);

        // Update to NULL
        $I->sendPUT('/v1/staff/100', ['education_level_id' => null]);

        $I->canSeeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'success' => true,
            'data' => [
                'education_level_id' => null
            ]
        ]);

        // Update skip attribute
        $I->sendPUT('/v1/staff/100', []);

        $I->canSeeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'success' => true,
            'data' => [
                'education_level_id' => null
            ]
        ]);
    }

    public function staffDeleteStaff(ApiTester $I)
    {
        $I->amStaff();

        $I->sendDELETE($this->endpointStaff . '/5');
        $I->canSeeResponseCodeIs(204);
    }

    public function canListStaffFilterByRole(ApiTester $I)
    {
        $I->amStaff('staffprov');

        $I->sendGET('/v1/staff?role_id=trainer');

        $I->canSeeResponseCodeIs(200);
        $I->seeHttpHeader('X-Pagination-Total-Count', 1);

        $I->seeResponseContainsJson([
            'success' => true,
            'data' => [
                'items' => [
                    ['id' => 40]
                ]
            ]
        ]);
    }
}
