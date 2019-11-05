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

    /**
     * @var string
     */
    protected $relativeFilePath;

    public function rules()
    {
        $uploadMaxSize = Yii::$app->params['upload_max_size'];

        return [
            [['file'], 'required'],
            [
                'file',
                'file',
                'skipOnEmpty' => false,
                'extensions' => 'image/jpeg, image/jpg, image/png, pdf, doc, docx, ppt, pptx',
                'checkExtensionByMimeType' => false,
                'maxSize' => $uploadMaxSize,
            ],
        ];
    }

    /**
     * @return string
     */
    public function createFilePath()
    {
        $filename = time() . '-' . Str::random(32);
        $extension = $this->file->extension;

        return sprintf('general/%s.%s', $filename, $extension);
    }

    /**
     * @return bool
     */
    public function upload()
    {
        if ($this->validate()) {
            $this->relativeFilePath = $this->createFilePath();

            $stream = fopen($this->file->tempName, 'r+');
            Yii::$app->fs->put($this->relativeFilePath, $stream);

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
        return $this->relativeFilePath;
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
