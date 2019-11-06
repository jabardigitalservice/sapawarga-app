<?php

namespace app\models\Attachment;

use Yii;
use app\models\AttachmentForm;

class NewsPhotoForm extends AttachmentForm
{
    public function rules()
    {
        $uploadMaxSize = Yii::$app->params['upload_max_size'];

        return [
            [['file', 'type'], 'required'],
            [
                'file',
                'image',
                'minWidth'    => 1280,
                'maxWidth'    => 1280,
                'minHeight'   => 720,
                'maxHeight'   => 720,
                'skipOnEmpty' => false,
                'mimeTypes'   => 'image/jpeg, image/jpg, image/png',
                'maxSize'     => $uploadMaxSize,
            ],
        ];
    }

    /**
     * @param $filePath
     *
     * @return \Intervention\Image\Image|\Intervention\Image\ImageManager
     */
    public function cropAndResizePhoto($filePath)
    {
        return $this->imageProcessor->make($filePath)->fit(1280, 720);
    }
}
