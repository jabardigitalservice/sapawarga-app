<?php

namespace app\modules\v1\controllers\Concerns;

use app\models\User;
use app\models\UserPhotoUploadForm;
use Yii;
use yii\web\UploadedFile;

trait UserPhotoUpload
{
    protected function processPhotoUpload(User $user = null)
    {
        $model = new UserPhotoUploadForm();
        $model->load(Yii::$app->getRequest()->getBodyParams(), '');
        $model->file = UploadedFile::getInstanceByName('image');
        $model->type = $model;

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
                'photo_url'  => $url,
            ];

            if ($user !== null) {
                $user->photo_url = $relativePath;
                $user->save(false);
            }

            return $responseData;
        }

        $response = Yii::$app->getResponse();
        $response->setStatusCode(400);

        return $response;
    }
}
