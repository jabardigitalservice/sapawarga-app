<?php

use app\models\User;


/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
*/
class ApiTester extends \Codeception\Actor
{
    use _generated\ApiTesterActions;

    public function amUser($username = null)
    {
        if ($username === null) {
            $username = 'user';
        }

        $accessToken = $this->getAccessToken($username);

        $I = $this;
        $I->amBearerAuthenticated($accessToken);
    }

    public function amStaff($username = null)
    {
        if ($username === null) {
            $username = 'admin';
        }

        $accessToken = $this->getAccessToken($username);

        $I = $this;
        $I->amBearerAuthenticated($accessToken);
    }

    protected function getAccessToken($username)
    {
        $user = User::findByUsername($username);
        $user->generateAccessTokenAfterUpdatingClientInfo(true);

        return $user->access_token;
    }
}
