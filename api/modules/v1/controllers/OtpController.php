<?php

namespace app\modules\v1\controllers;

use yii\filters\AccessControl;

class OtpController extends RestController
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['access'] = [
            'class' => AccessControl::class,
            'only' => ['request', 'verify'],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['request', 'verify'],
                    'roles' => ['@'],
                ],
            ],
        ];

        return $behaviors;
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
}
