<?php

namespace app\models\Attachment;

use Yii;
use app\models\AttachmentForm;

class NewsHoaxPhotoForm extends AttachmentForm
{
    public function rules()
    {
        $uploadMaxSize = Yii::$app->params['upload_max_size'];

        return [
            [['file', 'type'], 'required'],
            [['file'], 'file',
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
        // Make image resize with proporsional ratio
        return $this->imageProcessor->make($filePath)->resize(640, 640, function ($constraint) {
            $constraint->aspectRatio();
        });
    }
}
