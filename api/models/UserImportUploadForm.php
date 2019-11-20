<?php

namespace app\models;

use Yii;
use yii\base\Model;
use yii\web\UploadedFile;

class UserImportUploadForm extends Model
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
                'extensions' => 'xlsx',
                'checkExtensionByMimeType' => false,
                'maxSize' => $uploadMaxSize,
            ],
        ];
    }

    public function upload()
    {
        $contents = file_get_contents($this->file->tempName);

        $filePath = sprintf('import/user/%s-%s.xlsx', $this->file->baseName, date('Ymd-His'));

        if (Yii::$app->fs->put($filePath, $contents) === true) {
            return $filePath;
        }

        return false;
    }
}
