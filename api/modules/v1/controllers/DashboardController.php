<?php

namespace app\modules\v1\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\filters\auth\CompositeAuth;
use app\filters\auth\HttpBearerAuth;

use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

use app\models\AspirasiDashboard;

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
            'only' => ['index', 'usulan'], //only be applied to
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['index', 'usulan'],
                    'roles' => ['dashboardList'],
                ],
            ],
        ];

        return $behaviors;
    }

    public function actionUsulantop()
    {
        $params = Yii::$app->request->getQueryParams();

        $usulanTop = new AspirasiDashboard();

        return $usulanTop->getAspirasiTop($params);
    }
}
