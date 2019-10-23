<?php

namespace app\modules\v1\controllers;

use app\models\NewsHoax;
use app\models\NewsHoaxSearch;
use Yii;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;

/**
 * NewsHoaxController implements the CRUD actions for NewsHoax model.
 */
class NewsHoaxController extends ActiveController
{
    public $modelClass = NewsHoax::class;

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        return $this->behaviorCors($behaviors);
    }

    protected function behaviorAccess($behaviors)
    {
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only'  => ['index', 'view', 'create', 'update', 'delete'],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['index', 'view', 'create', 'update', 'delete'],
                    'roles' => ['admin', 'newsSaberhoaxManage'],
                ],
                [
                    'allow' => true,
                    'actions' => ['index', 'view'],
                    'roles' => ['newsSaberhoaxList'],
                ],
            ],
        ];

        return $behaviors;
    }

    public function actions()
    {
        $actions = parent::actions();

        // Override Delete Action
        unset($actions['delete']);

        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];
        $actions['view']['findModel']            = [$this, 'findModel'];

        return $actions;
    }

    /**
     * Delete entity with soft delete / status flagging
     *
     * @param $id
     * @return string
     * @throws \yii\web\ForbiddenHttpException
     * @throws \yii\web\NotFoundHttpException
     * @throws \yii\web\ServerErrorHttpException
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        return parent::applySoftDelete($model);
    }

    /**
     * @param $id
     * @return mixed|\app\models\News
     * @throws \yii\web\NotFoundHttpException
     */
    public function findModel($id)
    {
        $query = NewsHoax::find()
            ->where(['id' => $id])
            ->andWhere(['!=', 'status', NewsHoax::STATUS_DELETED]);

        $user = Yii::$app->user;
        if ($user->can('newsSaberHoaxList')
            && $user->can('newsSaberhoaxManage') === false) {
            $query = $query->andWhere(['!=', 'status', NewsHoax::STATUS_DISABLED]);
        }

        $model = $query->one();

        if ($model === null) {
            throw new NotFoundHttpException("Object not found: $id");
        }

        return $model;
    }


    public function prepareDataProvider()
    {
        $params = Yii::$app->request->getQueryParams();
        $user   = Yii::$app->user;
        $search = new NewsHoaxSearch();

        if ($user->can('newsSaberHoaxList')
            && $user->can('newsSaberhoaxManage') === false) {
            $search->scenario = NewsHoaxSearch::SCENARIO_LIST_USER;
        }

        return $search->search($params);
    }
}
