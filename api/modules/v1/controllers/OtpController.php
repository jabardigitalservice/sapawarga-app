<?php

namespace app\modules\v1\controllers;

use GuzzleHttp\Client;
use yii\filters\AccessControl;

class OtpController extends RestController
{
    const TIMEOUT = 15.0;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['access'] = [
            'class' => AccessControl::class,
            'only' => ['check-balance', 'request', 'verify'],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['request', 'verify'],
                    'roles' => ['@'],
                ],
                [
                    'allow' => true,
                    'actions' => ['check-balance'],
                    'roles' => ['admin'],
                ],
            ],
        ];

        return $behaviors;
    }

    /**
     * Checks OTP balance from server
     * POST /otp/request
     *
     * @return string
     */
    public function actionCheckBalance()
    {
        return $this->createPostRequest(
            '/sms/api_sms_otp_balance_json.php',
            ['apikey' => getenv('SMS_API_KEY')]
        );
    }

    /**
     * Requests OTP code from server
     * POST /otp/request
     *
     * @return string
     */
    public function actionRequest()
    {
        return 'ok';
    }

    /**
     * Verifies OTP code sent by client
     * POST /otp/verify
     *
     * @return string
     */
    public function actionVerify()
    {
        return 'ok';
    }

    protected function createPostRequest($uri, $jsonBody)
    {
        $client = new Client([
            'base_uri' => getenv('SMS_HOST'),
            'timeout'  => self::TIMEOUT,
        ]);

        $body = [
            'json' => $jsonBody
        ];

        $response = $client->post($uri, $body);
        $resBody = $response->getBody();

        return (string)$resBody;
    }
}
