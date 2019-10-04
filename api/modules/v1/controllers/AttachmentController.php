<?php

namespace app\modules\v1\controllers;

use app\filters\auth\HttpBearerAuth;
use app\models\Attachment\AspirasiPhotoForm;
use app\models\Attachment\NewsPhotoForm;
use app\models\Attachment\PhoneBookPhotoForm;
use app\models\Attachment\BannerPhotoForm;
use app\models\AttachmentForm;
use app\models\UserPhotoUploadForm;
use Illuminate\Support\Arr;
use Yii;
use yii\filters\AccessControl;
use yii\filters\auth\CompositeAuth;
use yii\web\UploadedFile;

class AttachmentController extends ActiveController
{
    public $modelClass = AttachmentForm::class;

    public function actions()
    {
        return [
            'options' => [
                'class' => 'yii\rest\OptionsAction',
            ],
        ];
    }

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['verbs'] = [
            'class'   => \yii\filters\VerbFilter::className(),
            'actions' => [
                'create' => ['post'],
            ],
        ];

        return $this->behaviorCors($behaviors);
    }

    protected function behaviorAccess($behaviors)
    {
        // setup access
        $behaviors['access'] = [
            'class' => AccessControl::className(),
            'only'  => ['create'], //only be applied to
            'rules' => [
                [
                    'allow'   => true,
                    'actions' => ['create'],
                    'roles'   => ['@'], //all authenticated users
                ],
            ],
        ];

        return $behaviors;
    }

    public function actionCreate()
    {
        $type = Yii::$app->request->post('type');

        $model = $this->getModelType($type);

        if ($model === null) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(400);

            return ['Model type not set.'];
        }

        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        $model->file = UploadedFile::getInstanceByName('file');

        if (!$model->validate()) {
            $response = Yii::$app->getResponse();
            $response->setStatusCode(422);

            return $model->getErrors();
        }

        if ($model->upload()) {
            $relativePath = $model->getRelativeFilePath();
            $url = $model->getFileUrl();

            $responseData = [
                'path' => $relativePath,
                'url'  => $url,
            ];

            return $responseData;
        }

        $response = Yii::$app->getResponse();
        $response->setStatusCode(400);
    }

    protected function getModelType($type)
    {
        $types = [
            'phonebook_photo' => PhoneBookPhotoForm::class,
            'aspirasi_photo'  => AspirasiPhotoForm::class,
            'news_photo'      => NewsPhotoForm::class,
            'banner_photo'    => BannerPhotoForm::class,
            'user_photo'      => UserPhotoUploadForm::class,
        ];

        if (Arr::has($types, $type)) {
            return new $types[$type];
        }

        return null;
    }
}
