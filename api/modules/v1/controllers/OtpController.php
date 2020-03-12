<?php

namespace app\modules\v1\controllers;

use GuzzleHttp\Client;
use yii\filters\AccessControl;

class OtpController extends RestController
{
    const TIMEOUT = 30.0;

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
        $response = $this->createPostRequest(
            '/sms/api_sms_otp_balance_json.php',
            ['apikey' => getenv('SMS_API_KEY')]
        );

        $jsonResponse = json_decode($response, true);
        return $jsonResponse['balance_respon'][0];
    }

    /**
     * Requests OTP code from server
     * POST /otp/request
     *
     * @return string
     */
    public function actionRequest()
    {
        $params = Yii::$app->request->getQueryParams();
        $phone = Arr::get($params, 'phone'); //number with country code, without the '+' (e.g. 6281xxxxxxxxx)
        $otpCode = 'xxxxxx';
        $signature = getenv('MOBILE_APP_SIGNATURE');
        $message = "Ini adalah kode untuk verifikasi Sapawarga. Jangan memberitahukan kode ini ke siapapun: {$otpCode}\n{$signature}";
        $data = [
            'apikey' => getenv('SMS_API_KEY'),
            'callbackurl' => '',
            'datapacket' => [[
                'number' => trim($phone),
                'message' => $message,
            ]],
        ];

        $response = $this->createPostRequest(
            '/sms/api_sms_otp_send_json.php',
            $data
        );

        $jsonResponse = json_decode($response, true);
        return $jsonResponse['sending_respon'][0];
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
