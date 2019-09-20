<?php

namespace app\models;

use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Yii;
use yii\base\Model;
use yii\web\UploadedFile;

/**
 * User Photo Upload form
 */
class UserPhotoUploadForm extends AttachmentForm
{
    /**
     * @param $filePath
     *
     * @return \Intervention\Image\Image|\Intervention\Image\ImageManager
     */
    public function cropAndResizePhoto($filePath)
    {
        return $this->imageProcessor->make($filePath)->fit(640, 640);
    }

    /**
     * @param \app\models\User $user
     * @param $relativePath
     * @return string
     */
    public function setUserProfilePhoto($user, $relativePath)
    {
        $user->photo_url = $relativePath;
        $user->save(false);

        return $relativePath;
    }

}
