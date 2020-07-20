<?php

namespace app\modules\v1\controllers;

use app\filters\auth\HttpBearerAuth;
use Jdsteam\Sapawarga\Filters\RecordLastActivity;
use yii\db\ActiveRecord;
use yii\filters\auth\CompositeAuth;
use yii\filters\VerbFilter;
use yii\rest\ActiveController as BaseActiveController;
use yii\web\NotFoundHttpException;
use yii\web\ForbiddenHttpException;
use app\modules\v1\repositories\LikeRepository;
use Yii;

class ActiveController extends BaseActiveController
{
    /**
     * @var string|array the configuration for creating the serializer that formats the response data.
     */
    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['verbs'] = [
            'class' => VerbFilter::className(),
            'actions' => [
                'index' => ['get'],
                'view' => ['get'],
                'create' => ['post'],
                'update' => ['put'],
                'delete' => ['delete'],
                'public' => ['get'],
            ],
        ];

        $behaviors['authenticator'] = [
            'class'       => CompositeAuth::className(),
            'authMethods' => [
                HttpBearerAuth::className(),
            ],
        ];

        // Disable temporary, for performance checking
        // Record last activity for all controllers derived from ActiveController
        // $behaviors['recordLastActivity'] = [
        //     'class' => RecordLastActivity::class,
        // ];

        return $behaviors;
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
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
                'Access-Control-Request-Headers' => ['*'],
            ],
        ];

        // re-add authentication filter
        $behaviors['authenticator'] = $auth;
        // avoid authentication on CORS-pre-flight requests (HTTP OPTIONS method)
        $behaviors['authenticator']['except'] = ['options', 'public'];

        return $this->behaviorAccess($behaviors);
    }

    /**
     * Wrapper override function for actionView's findModel
     *
     * @param string $id
     * @param $model
     * @return mixed|ActiveRecord
     * @throws \yii\web\NotFoundHttpException
     */
    public function findModel(string $id, $model)
    {
        $searchedModel = $model::find()
            ->where(['id' => $id])
            ->andWhere(['!=', 'status', $model::STATUS_DELETED])
            ->one();

        if ($searchedModel === null) {
            throw new NotFoundHttpException("Object not found: $id");
        }

        return $searchedModel;
    }

    protected function applySoftDelete($model)
    {
        $model->status = $model::STATUS_DELETED;

        if ($model->save(false) === false) {
            throw new ServerErrorHttpException('Failed to delete the object for unknown reason.');
        }

        $response = Yii::$app->getResponse();
        $response->setStatusCode(204);

        return 'ok';
    }

    protected function checkAccessDefault($action, $model = null, $params = [])
    {
        $authUser = Yii::$app->user;
        $authUserId = $authUser->id;

        // Admin, staffprov can do everything
        if ($authUser->can('admin') || $authUser->can('staffProv')) {
            return true;
        }

        if (in_array($action, ['update', 'delete']) && $model->created_by !== \Yii::$app->user->id) {
            throw new ForbiddenHttpException(Yii::t('app', 'error.role.permission'));
        }
    }

    protected function setLikeAndCount($id, $type, $model)
    {
        $repository = new LikeRepository();
        $setLikeUnlike = $repository->setLikeUnlike($id, $type);
        $likesCount = $repository->getLikesCount($id, $type);

        // Update likes_count
        $updateLikesCount = $model::findOne($id);
        $updateLikesCount->likes_count = $likesCount;
        $updateLikesCount->save();
    }
}
