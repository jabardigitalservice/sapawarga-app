<?php

namespace app\modules\v1\controllers\pub;

use app\models\Area;
use app\models\AreaSearch;
use yii\filters\AccessControl;
use app\modules\v1\controllers\ActiveController as ActiveController;

/**
 * AreaController implements list and detail Area
 */
class AreaController extends ActiveController
{
    public $modelClass = Area::class;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        return $this->behaviorCors($behaviors);
    }

    protected function behaviorAccess($behaviors)
    {
        $behaviors['authenticator']['except'] = [
            'index', 'view'
        ];

        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only' => ['index', 'view'],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['index', 'view'],
                    'roles'   => ['?'],
                ],
            ],
        ];

        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();
        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];
        return $actions;
    }

    public function prepareDataProvider()
    {
        $search = new AreaSearch();

        return $search->search(\Yii::$app->request->getQueryParams());
    }
}
