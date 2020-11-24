<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;

class SiteController extends Controller
{
    public function actionPing()
    {
        $response = new Response();
        $response->statusCode = 200;
        $response->data = Yii::t('app', 'pong (' . getenv('APP_VERSION') . ')');

        return $response;
    }

    public function actionStorage()
    {
        $response = new Response();
        $response->statusCode = 200;
        $response->data = file_get_contents(__DIR__ . '/../web/assets/version.json');

        return $response;
    }

    public function actionError()
    {
        $response = new Response();
        $response->statusCode = 400;
        $response->data = json_encode(
            [
                'name' => 'Bad Request',
                'message' => Yii::t('app', 'The system could not process your request. Please check and try again.'),
                'code' => 0,
                'status' => 400,
                'type' => 'yii\\web\\BadRequestHttpException'
            ]
        );

        return $response;
    }

    public function actionTestException()
    {
      throw new \Exception('test exception on purpose');
    }
}
