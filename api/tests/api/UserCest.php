<?php

class UserCest
{
    private $endpointLogin = '/v1/user/login';
    private $endpointProfile = '/v1/user/me';

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
        ], '$.data');
    }

    public function userUpdateProfile(ApiTester $I)
    {
        // reset 'user' column
        Yii::$app->db->createCommand()->checkIntegrity(false)->execute();
        Yii::$app->db->createCommand('TRUNCATE auth_assignment')->execute();
        Yii::$app->db->createCommand('TRUNCATE user')->execute();
        $sql = file_get_contents(__DIR__ . '/../../migrations/seeder/user_and_permission.sql');
        Yii::$app->db->createCommand($sql)->execute();

        $I->amUser('staffrw3');

        $I->sendPOST("{$this->endpointProfile}", [
            'username' => 'staffrw3',
            'name' => 'Name Edited',
        ]);

        $I->canSeeResponseCodeIs(200);
        $I->seeResponseIsJson();

        $I->seeResponseContainsJson([
            'success' => true,
            'status'  => 200,
        ]);
    }
}
