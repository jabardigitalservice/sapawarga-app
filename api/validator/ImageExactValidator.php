<?php

namespace app\validator;

use Yii;
use yii\validators\ImageValidator;
use yii\web\UploadedFile;

class ImageExactValidator extends ImageValidator
{
    public $exactWidth;
    public $exactHeight;
    public $dimensionNotValid;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        if ($this->notImage === null) {
            $this->notImage = Yii::t('app', 'error.image.invalid_format');
        }

        if ($this->dimensionNotValid === null) {
            $this->dimensionNotValid = Yii::t('app', 'error.image.should_exact', [
                'width'  => $this->exactWidth,
                'height' => $this->exactHeight,
            ]);
        }
    }

    /**
     * Validates an image file.
     * @param UploadedFile $image uploaded file passed to check against a set of rules
     * @return array|null the error message and the parameters to be inserted into the error message.
     * Null should be returned if the data is valid.
     */
    protected function validateImage($image)
    {
        if (false === ($imageInfo = getimagesize($image->tempName))) {
            return [$this->notImage, ['file' => $image->name]];
        }

        [$width, $height] = $imageInfo;

        if ($width === 0 || $height === 0) {
            return [$this->notImage, ['file' => $image->name]];
        }

        if ($this->exactWidth !== $width &&
            $this->exactHeight !== $height
        ) {
            return [$this->dimensionNotValid, [
                'file'   => $image->name,
                'width'  => $this->exactWidth,
                'height' => $this->exactHeight
            ]];
        }

        return null;
    }
}