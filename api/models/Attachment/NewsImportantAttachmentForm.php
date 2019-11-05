<?php

namespace app\models\Attachment;

use Yii;
use yii\base\Model;
use yii\web\UploadedFile;
use Illuminate\Support\Str;

class NewsImportantAttachmentForm extends Model
{
    /**
     * @var UploadedFile
     */
    public $file;

    public function rules()
    {
        $uploadMaxSize = Yii::$app->params['upload_max_size'];

        return [
            [['file'], 'required'],
            [
                'file',
                'file',
                'skipOnEmpty' => false,
                'extensions' => 'image/jpeg, image/jpg, image/png, pdf, doc, docx',
                'checkExtensionByMimeType' => false,
                'maxSize' => $uploadMaxSize,
            ],
        ];
    }

    public function upload()
    {
        $filename = $this->file->baseName . '.' . $this->file->extension;

        if ($this->validate()) {
            // Save temp file
            $tempFilePath = Yii::getAlias('@webroot/storage') . '/temp-' . $filename;
            $this->file->saveAs($tempFilePath);

            return true;
        } else {
            return false;
        }
    }

    /**
     * @return string
     */
    public function getRelativeFilePath()
    {
        $publicBaseUrl = Yii::$app->params['storagePublicBaseUrl'];

        // Open temp and save to flysystem
        $filename = $this->file->baseName . '.' .  $this->file->extension;
        $tempFilePath = Yii::getAlias('@webroot/storage') . '/temp-' . $filename;

        $filename = time() . '.' .  $this->file->extension;
        $stream = fopen($tempFilePath, 'r+');
        Yii::$app->fs->put($filename, $stream);

        return $filename;
    }

    /**
     * @return string
     */
    public function getFileUrl()
    {
        $publicBaseUrl = Yii::$app->params['storagePublicBaseUrl'];

        return "{$publicBaseUrl}/{$this->getRelativeFilePath()}";
    }
}
