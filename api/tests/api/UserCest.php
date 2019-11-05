<?php

class UserCest
{
    private $endpointLogin = '/v1/user/login';
    private $endpointProfile = '/v1/user/me';

    public function _before(ApiTester $I)
    {
        Yii::$app->db->createCommand()->checkIntegrity(false)->execute();
        Yii::$app->db->createCommand('TRUNCATE auth_assignment')->execute();
        Yii::$app->db->createCommand('TRUNCATE user')->execute();
        $sql = file_get_contents(__DIR__ . '/../../migrations/seeder/user_and_permission.sql');
        Yii::$app->db->createCommand($sql)->execute();
    }

    protected function login(ApiTester $I)
    {
        $I->sendPOST($this->endpointLogin, [
            'LoginForm' => [
                'username' => 'user',
                'password' => '123456',
            ]
        ]);

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status' => 200,
        ]);

        $I->seeResponseMatchesJsonType([
            'id' => 'integer',
            'access_token' => 'string',
        ], '$.data');

        $token = $I->grabDataFromResponseByJsonPath('$..data.access_token');
        $token = $token[0];

        $I->amBearerAuthenticated($token);
    }

    public function userLoginInvalidFields(ApiTester $I)
    {
        $I->sendPOST($this->endpointLogin);

        $I->seeResponseCodeIs(422);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => false,
            'status' => 422,
        ]);

        $I->sendPOST($this->endpointLogin, [
            'LoginForm' => [
                'username' => 'user',
            ]
        ]);

        $I->seeResponseCodeIs(422);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => false,
            'status' => 422,
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
            'status' => 422,
        ]);
    }

    public function userLoginInvalidCredentials(ApiTester $I)
    {
        $I->sendPOST($this->endpointLogin, [
            'LoginForm' => [
                'username' => 'user',
                'password' => '1234567',
            ]
        ]);

        $I->seeResponseCodeIs(422);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => false,
            'status' => 422,
        ]);
    }

    public function userLoginInactiveUsername(ApiTester $I)
    {
        $I->sendPOST($this->endpointLogin, [
            'LoginForm' => [
                'username' => 'user.inactive',
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

    /**
     * @before login
     */
    public function userLogin(ApiTester $I)
    {
    }

    /**
     * @before login
     */
    public function userGetProfile(ApiTester $I)
    {
        $I->sendGET($this->endpointProfile);
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeResponseMatchesJsonType([
            'id' => 'integer',
            'username' => 'string|null',
            'email' => 'string|null',
            'photo_url' => 'string|null',
            'name' => 'string|null',
            'phone' => 'string|null',
            'address' => 'string|null',
            'rw' => 'string|null',
            'kelurahan' => 'array',
            'kecamatan' => 'array',
            'kabkota' => 'array',
            'facebook' => 'string|null',
            'twitter' => 'string|null',
            'instagram' => 'string|null',
            'last_login_at' => 'integer|null',
            'password_updated_at' => 'integer|null',
            'profile_updated_at' => 'integer|null',
        ], '$.data');
    }

    public function userUpdateProfile(ApiTester $I)
    {
        $I->haveInDatabase('user', [
            'id'                => 100,
            'username'          => 'user.test',
            'name'              => 'Test User',
            'password_hash'     => '$2y$13$9Gouh1ZbewVEh4bQIGsifOs8/RWW/7RIs0CAGNd7tapXFm9.WxiXS',
            'email'             => 'user@test.com',
            'unconfirmed_email' => 'user@test.com',
            'confirmed_at'      => time(),
            'role'              => 50,
            'phone'             => 'phone',
            'address'           => 'address',
            'birth_date'        => null,
            'status'            => 10,
            'created_at'        => time(),
            'updated_at'        => time(),
        ]);

        $I->amUser('user.test');

        $I->sendPOST('/v1/user/me', [
            'UserEditForm' => [
                'birth_date' => '1988-11-15',
                'phone' => '',
                'address' => null,
            ]
        ]);

        $I->canSeeResponseCodeIs(200);

        $I->seeInDatabase('user', [
            'id'         => 100,
            'username'   => 'user.test',
            'name'       => 'Test User',
            'email'      => 'user@test.com',
            'birth_date' => '1988-11-15',
            'phone'      => null,
            'address'    => null,
        ]);

        // Set to null
        $I->sendPOST('/v1/user/me', [
            'UserEditForm' => [
                'birth_date' => null,
            ]
        ]);

        $I->canSeeResponseCodeIs(200);

        $I->seeInDatabase('user', [
            'id'         => 100,
            'username'   => 'user.test',
            'email'      => 'user@test.com',
            'birth_date' => null,
            'phone'      => null,
            'address'    => null,
        ]);
    }

    public function userUpdateProfileChangePassword(ApiTester $I)
    {
        $I->haveInDatabase('user', [
            'id'                => 100,
            'username'          => 'user.test',
            'name'              => 'Test User',
            'password_hash'     => '$2y$13$9Gouh1ZbewVEh4bQIGsifOs8/RWW/7RIs0CAGNd7tapXFm9.WxiXS',
            'email'             => 'user@test.com',
            'unconfirmed_email' => 'user@test.com',
            'confirmed_at'      => time(),
            'role'              => 50,
            'phone'             => 'phone',
            'address'           => 'address',
            'birth_date'        => null,
            'status'            => 10,
            'created_at'        => time(),
            'updated_at'        => time(),
        ]);

        $I->amUser('user.test');

        $I->sendPOST('/v1/user/me', [
            'UserEditForm' => [
                'password' => '1234567',
            ]
        ]);

        $I->canSeeResponseCodeIsSuccessful();

        $I->seeInDatabase('user', [
            'id'         => 100,
            'username'   => 'user.test',
            'name'       => 'Test User',
            'email'      => 'user@test.com',
            'birth_date' => null,
            'phone'      => 'phone',
            'address'    => 'address',
        ]);

        $I->sendPOST('/v1/user/login', [
            'LoginForm' => [
                'username' => 'user.test',
                'password' => '1234567',
            ]
        ]);

        $I->canSeeResponseCodeIsSuccessful();
    }

    public function userUpdateProfileChangePasswordCaseTwo(ApiTester $I)
    {
        $I->haveInDatabase('user', [
            'id'                => 100,
            'username'          => 'user.test',
            'name'              => 'Test User',
            'password_hash'     => '$2y$13$9Gouh1ZbewVEh4bQIGsifOs8/RWW/7RIs0CAGNd7tapXFm9.WxiXS',
            'email'             => 'user@test.com',
            'unconfirmed_email' => 'user@test.com',
            'confirmed_at'      => time(),
            'role'              => 50,
            'phone'             => 'phone',
            'address'           => 'address',
            'birth_date'        => null,
            'status'            => 10,
            'created_at'        => time(),
            'updated_at'        => time(),
        ]);

        $I->amUser('user.test');

        $I->sendPOST('/v1/user/me', [
            'UserEditForm' => [
                'password' => '1234567',
            ]
        ]);

        $I->canSeeResponseCodeIsSuccessful();

        $I->seeInDatabase('user', [
            'id'       => 100,
            'username' => 'user.test',
        ]);

        $I->sendPOST('/v1/user/login', [
            'LoginForm' => [
                'username' => 'user.test',
                'password' => 'xxx',
            ]
        ]);

        $I->canSeeResponseCodeIsClientError();
    }

    public function userUpdateProfileChangePasswordCaseThree(ApiTester $I)
    {
        $I->haveInDatabase('user', [
            'id'                => 100,
            'username'          => 'user.test',
            'name'              => 'Test User',
            'password_hash'     => '$2y$13$9Gouh1ZbewVEh4bQIGsifOs8/RWW/7RIs0CAGNd7tapXFm9.WxiXS',
            'email'             => 'user@test.com',
            'unconfirmed_email' => 'user@test.com',
            'confirmed_at'      => time(),
            'role'              => 50,
            'phone'             => 'phone',
            'address'           => 'address',
            'birth_date'        => null,
            'status'            => 10,
            'created_at'        => time(),
            'updated_at'        => time(),
        ]);

        $I->amUser('user.test');

        $I->sendPOST('/v1/user/me', [
            'UserEditForm' => [
                'password' => '',
            ]
        ]);

        $I->canSeeResponseCodeIsSuccessful();

        $I->seeInDatabase('user', [
            'id'       => 100,
            'username' => 'user.test',
        ]);

        $I->sendPOST('/v1/user/login', [
            'LoginForm' => [
                'username' => 'user.test',
                'password' => '123456',
            ]
        ]);

        $I->canSeeResponseCodeIsSuccessful();
    }

    public function userUpdateProfileChangePasswordCaseFour(ApiTester $I)
    {
        $I->haveInDatabase('user', [
            'id'                => 100,
            'username'          => 'user.test',
            'name'              => 'Test User',
            'password_hash'     => '$2y$13$9Gouh1ZbewVEh4bQIGsifOs8/RWW/7RIs0CAGNd7tapXFm9.WxiXS',
            'email'             => 'user@test.com',
            'unconfirmed_email' => 'user@test.com',
            'confirmed_at'      => time(),
            'role'              => 50,
            'phone'             => 'phone',
            'address'           => 'address',
            'birth_date'        => null,
            'status'            => 10,
            'created_at'        => time(),
            'updated_at'        => time(),
        ]);

        $I->amUser('user.test');

        $I->sendPOST('/v1/user/me', [
            'UserEditForm' => [
                'username' => 'user.test',
            ]
        ]);

        $I->canSeeResponseCodeIsSuccessful();

        $I->seeInDatabase('user', [
            'id'       => 100,
            'username' => 'user.test',
        ]);

        $I->sendPOST('/v1/user/login', [
            'LoginForm' => [
                'username' => 'user.test',
                'password' => '123456',
            ]
        ]);

        $I->canSeeResponseCodeIsSuccessful();
    }

    public function userChangePasswordSucessTest(ApiTester $I)
    {
        $I->amUser('staffrw');

        // Change password first time
        $I->sendPOST('/v1/user/me/change-password', [
            'password' => '1234567',
            'password_confirmation' => '1234567',
        ]);
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        // Change password second time or more with old password
        $I->sendPOST('/v1/user/me/change-password', [
            'password_old' => '1234567',
            'password' => '12345678',
            'password_confirmation' => '12345678',
        ]);
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);
    }

    public function userNotSendPasswordConfirmationFail(ApiTester $I)
    {
        $I->amUser('staffrw');

        $I->sendPOST('/v1/user/me/change-password', [
            'password' => '123456',
        ]);
        $I->canSeeResponseCodeIs(422);
        $I->seeResponseContainsJson([
            'success' => false,
            'status'  => 422,
        ]);
    }

    public function userSameNewAndOldPasswordFail(ApiTester $I)
    {
        $I->amUser('staffrw');

        $I->sendPOST('/v1/user/me/change-password', [
            'password' => '123456',
            'password_confirmation' => '123456',
        ]);
        $I->canSeeResponseCodeIs(422);
        $I->seeResponseContainsJson([
            'success' => false,
            'status'  => 422,
        ]);
    }

    public function userChangeProfileSuccess(ApiTester $I)
    {
        $I->amUser('staffrw16');

        $I->sendPOST('/v1/user/me/change-profile', [
            'name' => 'name_edited',
            'email' => 'email_updated@demo.com',
            'phone' => '11112222',
            'address' => 'address_updated',
        ]);
        $I->canSeeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeInDatabase('user', [
            'name' => 'name_edited',
            'email' => 'email_updated@demo.com',
            'phone' => '11112222',
            'address' => 'address_updated',
        ]);
    }

    public function userChangeProfileNoDataFail(ApiTester $I)
    {
        $I->amUser('staffrw16');

        $I->sendPOST('/v1/user/me/change-profile', []);

        $I->canSeeResponseCodeIs(422);
        $I->seeResponseContainsJson([
            'success' => false,
            'status'  => 422,
        ]);
    }

    public function userAdminCanSeeSaberHoax(ApiTester $I)
    {
        $I->amStaff('admin');

        $I->sendGET('/v1/staff?name=saber');

        $I->canSeeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeHttpHeader('X-Pagination-Total-Count', 1);
    }

    public function userProvCanNotSeeSaberHoax(ApiTester $I)
    {
        $I->amStaff('staffprov');

        $I->sendGET('/v1/staff?name=saber');

        $I->canSeeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeHttpHeader('X-Pagination-Total-Count', 0);
    }

    public function userProvCanFilterProfileComplete(ApiTester $I)
    {
        $I->amStaff('staffprov');

        $I->sendGET('/v1/staff?profile_completed=true');

        $I->canSeeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);

        $I->seeHttpHeader('X-Pagination-Total-Count', 1);
    }

    public function userCanViewBirthdate(ApiTester $I)
    {
        $I->haveInDatabase('user', [
            'id'                => 100,
            'username'          => 'user.test',
            'password_hash'     => '$2y$13$9Gouh1ZbewVEh4bQIGsifOs8/RWW/7RIs0CAGNd7tapXFm9.WxiXS',
            'email'             => 'user@test.com',
            'unconfirmed_email' => 'user@test.com',
            'confirmed_at'      => time(),
            'role'              => 50,
            'birth_date'        => '1988-11-15',
            'status'            => 10,
            'created_at'        => time(),
            'updated_at'        => time(),
        ]);

        $I->amUser('user.test');

        $I->sendGET('/v1/user/me');

        $I->canSeeResponseCodeIs(200);
        $I->seeResponseContainsJson([
            'success' => true,
            'data' => [
                'birth_date' => '1988-11-15',
            ]
        ]);
    }

    public function userCanViewJobType(ApiTester $I)
    {
        $I->haveInDatabase('user', [
            'id'                => 100,
            'username'          => 'user.test',
            'password_hash'     => '$2y$13$9Gouh1ZbewVEh4bQIGsifOs8/RWW/7RIs0CAGNd7tapXFm9.WxiXS',
            'email'             => 'user@test.com',
            'unconfirmed_email' => 'user@test.com',
            'confirmed_at'      => time(),
            'role'              => 50,
            'job_type_id'       => 1,
            'status'            => 10,
            'created_at'        => time(),
            'updated_at'        => time(),
        ]);

        $I->amUser('user.test');

        $I->sendGET('/v1/user/me');

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

    public function userCanViewEducationLevel(ApiTester $I)
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

        $I->amUser('user.test');

        $I->sendGET('/v1/user/me');

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

    public function userCanUpdateBirthdate(ApiTester $I)
    {
        $I->haveInDatabase('user', [
            'id'                => 100,
            'username'          => 'user.test',
            'password_hash'     => '$2y$13$9Gouh1ZbewVEh4bQIGsifOs8/RWW/7RIs0CAGNd7tapXFm9.WxiXS',
            'email'             => 'user@test.com',
            'unconfirmed_email' => 'user@test.com',
            'confirmed_at'      => time(),
            'role'              => 50,
            'birth_date'        => null,
            'status'            => 10,
            'created_at'        => time(),
            'updated_at'        => time(),
        ]);

        $I->amUser('user.test');

        $I->sendPOST('/v1/user/me', [
            'UserEditForm' => [
                'birth_date' => '1988-11-15',
            ]
        ]);

        $I->canSeeResponseCodeIs(200);

        $I->seeInDatabase('user', [
            'id'         => 100,
            'username'   => 'user.test',
            'email'      => 'user@test.com',
            'birth_date' => '1988-11-15',
        ]);

        // Set to null
        $I->sendPOST('/v1/user/me', [
            'UserEditForm' => [
                'birth_date' => null,
            ]
        ]);

        $I->canSeeResponseCodeIs(200);

        $I->seeInDatabase('user', [
            'id'         => 100,
            'username'   => 'user.test',
            'email'      => 'user@test.com',
            'birth_date' => null,
        ]);
    }

    public function userCanUpdateJobType(ApiTester $I)
    {
        $I->haveInDatabase('user', [
            'id'                => 100,
            'username'          => 'user.test',
            'password_hash'     => '$2y$13$9Gouh1ZbewVEh4bQIGsifOs8/RWW/7RIs0CAGNd7tapXFm9.WxiXS',
            'email'             => 'user@test.com',
            'unconfirmed_email' => 'user@test.com',
            'confirmed_at'      => time(),
            'role'              => 50,
            'job_type_id'       => null,
            'status'            => 10,
            'created_at'        => time(),
            'updated_at'        => time(),
        ]);

        $I->amUser('user.test');

        $I->sendPOST('/v1/user/me', [
            'UserEditForm' => [
                'job_type_id' => 1,
            ]
        ]);

        $I->canSeeResponseCodeIs(200);

        $I->seeInDatabase('user', [
            'id'          => 100,
            'username'    => 'user.test',
            'email'       => 'user@test.com',
            'job_type_id' => 1,
        ]);

        // Set to NULL
        $I->sendPOST('/v1/user/me', [
            'UserEditForm' => [
                'job_type_id' => null,
            ]
        ]);

        $I->canSeeResponseCodeIs(200);

        $I->seeInDatabase('user', [
            'id'          => 100,
            'username'    => 'user.test',
            'email'       => 'user@test.com',
            'job_type_id' => null,
        ]);
    }

    public function userCanUpdateEducationLevel(ApiTester $I)
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

        $I->amUser('user.test');

        $I->sendPOST('/v1/user/me', [
            'UserEditForm' => [
                'education_level_id' => 1,
            ]
        ]);

        $I->canSeeResponseCodeIs(200);

        $I->seeInDatabase('user', [
            'id'                 => 100,
            'username'           => 'user.test',
            'email'              => 'user@test.com',
            'education_level_id' => 1,
        ]);

        // Set to null
        $I->sendPOST('/v1/user/me', [
            'UserEditForm' => [
                'education_level_id' => null,
            ]
        ]);

        $I->canSeeResponseCodeIs(200);

        $I->seeInDatabase('user', [
            'id'                 => 100,
            'username'           => 'user.test',
            'email'              => 'user@test.com',
            'education_level_id' => null,
        ]);
    }
}
