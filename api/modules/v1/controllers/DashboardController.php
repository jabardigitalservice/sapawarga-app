<?php

namespace app\modules\v1\controllers;

use Yii;
use Illuminate\Support\Arr;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\filters\auth\CompositeAuth;
use app\filters\auth\HttpBearerAuth;
use yii\web\NotAcceptableHttpException;
use yii\web\ServerErrorHttpException;

use app\models\AspirasiDashboard;
use app\models\PollingDashboard;

/**
 * DashboardController for implements the prepared data
 */
class DashboardController extends ActiveController
{
    public $modelClass = News::class;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['authenticator'] = [
            'class' => CompositeAuth::className(),
            'authMethods' => [
                HttpBearerAuth::className(),
            ],
        ];

        $behaviors['verbs'] = [
            'class' => VerbFilter::className(),
            'actions' => [
                'usulan' => ['get'],
            ],
        ];

        return $this->behaviorCors($behaviors);
    }

    protected function behaviorCors($behaviors)
    {
        // remove authentication filter
        $auth = $behaviors['authenticator'];
        unset($behaviors['authenticator']);

        // add CORS filter
        $behaviors['corsFilter'] = [
            'class' => \yii\filters\Cors::className(),
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
            ],
        ];

        // re-add authentication filter
        $behaviors['authenticator'] = $auth;
        // avoid authentication on CORS-pre-flight requests (HTTP OPTIONS method)
        $behaviors['authenticator']['except'] = ['options', 'public'];

        return $this->behaviorAccess($behaviors);
    }

    protected function behaviorAccess($behaviors)
    {
        // setup access
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => ['aspirasi-most-likes', 'aspirasi-counts', 'aspirasi-geo'], //only be applied to
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['aspirasi-most-likes', 'aspirasi-counts', 'aspirasi-geo'],
                    'roles' => ['dashboardList'],
                ],
            ],
        ];

        return $behaviors;
    }

    public function actionAspirasiMostLikes()
    {
        $params = Yii::$app->request->getQueryParams();

        $aspirasiMostLikes = new AspirasiDashboard();

        return $aspirasiMostLikes->getAspirasiMostLikes($params);
    }

    public function actionAspirasiCounts()
    {
        $params = Yii::$app->request->getQueryParams();

        $aspirasiCounts = new AspirasiDashboard();

        return $aspirasiCounts->getAspirasiCounts($params);
    }

    public function actionAspirasiGeo()
    {
        $params = Yii::$app->request->getQueryParams();

        $aspirasiGeo = new AspirasiDashboard();

        return $aspirasiGeo->getAspirasiGeo($params);
    }

    public function actionPollingLatest()
    {
        $params = Yii::$app->request->getQueryParams();

        $pollingLatest = new PollingDashboard();

        return $pollingLatest->getPollingLatest($params);
    }

    public function actionPollingChart()
    {
        $params = Yii::$app->request->getQueryParams();

        $pollingId = Arr::get($params, 'id');

        if (empty($pollingId)) {
            throw new NotAcceptableHttpException('Object not acceptable');
        }

        $pollingChart = new PollingDashboard();

        return $pollingChart->getPollingChart($params);
    }
}
