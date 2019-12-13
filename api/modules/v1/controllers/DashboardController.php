<?php

namespace app\modules\v1\controllers;

use Yii;
use Illuminate\Support\Arr;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\filters\auth\CompositeAuth;
use app\filters\auth\HttpBearerAuth;
use yii\web\ServerErrorHttpException;

use app\models\PollingDashboard;
use app\models\AspirasiDashboard;
use app\models\NewsDashboard;

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
            'only' => ['aspirasi-most-likes', 'polling-latest', 'aspirasi-counts', 'aspirasi-geo', 'news-most-likes'],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => [
                        'aspirasi-most-likes', 'polling-latest', 'aspirasi-counts', 'aspirasi-geo', 'news-most-likes'
                    ],
                    'roles' => ['dashboardList'],
                ],
            ],
        ];

        return $behaviors;
    }

    public function actionAspirasiMostLikes()
    {
        $params = Yii::$app->request->getQueryParams();
        $params = $this->filterByStaffLocation($params);

        $aspirasiMostLikes = new AspirasiDashboard();

        return $aspirasiMostLikes->getAspirasiMostLikes($params);
    }

    public function actionAspirasiCounts()
    {
        $params = Yii::$app->request->getQueryParams();
        $params = $this->filterByStaffLocation($params);

        $aspirasiCounts = new AspirasiDashboard();

        return $aspirasiCounts->getAspirasiCounts($params);
    }

    public function actionAspirasiCategoryCounts()
    {
        $params = Yii::$app->request->getQueryParams();
        $params = $this->filterByStaffLocation($params);

        $aspirasiCounts = new AspirasiDashboard();

        return $aspirasiCounts->getAspirasiCategoryCounts($params);
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
        $params = $this->filterByStaffLocation($params);

        $pollingLatest = new PollingDashboard();

        return $pollingLatest->getPollingLatest($params);
    }

    public function actionNewsMostLikes()
    {
        $params = Yii::$app->request->getQueryParams();

        $newsMostLikes = new NewsDashboard();

        return $newsMostLikes->getNewsMostLikes($params);
    }

    /**
     * Filtering dashboard by staff location kab kota
     *
     * @return $params
     */
    public function filterByStaffLocation($params)
    {
        $authUser = Yii::$app->user;
        $authUserModel = $authUser->identity;

        $authKabKotaId = $authUserModel->kabkota_id;
        $authKecId = $authUserModel->kec_id;
        $authKelId = $authUserModel->kel_id;

        if ($authUser->can('staffKabkota')) {
            $params['kabkota_id'] = $authKabKotaId;
        }

        return $params;
    }
}
