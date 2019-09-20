<?php

namespace app\models;

use creocoder\flysystem\Filesystem;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Yii;
use yii\base\Model;
use yii\web\UploadedFile;

class AttachmentForm extends Model
{
    /**
     * @var UploadedFile
     */
    public $file;

    public $type;

    /**
     * @var ImageManager
     */
    protected $imageProcessor;

    /**
     * @var Filesystem
     */
    protected $fs;

    /**
     * @var string
     */
    protected $relativeFilePath;

    public function rules()
    {
        $uploadMaxSize = Yii::$app->params['upload_max_size'];

        return [
            [['file', 'type'], 'required'],
            [
                'file',
                'file',
                'skipOnEmpty' => false,
                'mimeTypes'   => 'image/jpeg, image/jpg, image/png',
                'maxSize'     => $uploadMaxSize,
            ],
        ];
    }

    /**
     * @return bool
     */
    public function upload()
    {
        /**
         * @var Filesystem $filesystem
         */
        $filesystem = Yii::$app->fs;

        $imageProcessor = new ImageManager();

        $this->setFilesystem($filesystem);
        $this->setImageProcessor($imageProcessor);

        $tempFilePath = $this->file->tempName;

        return $this->save($tempFilePath);
    }

    /**
     * @param string $tempFilePath
     * @return bool
     */
    public function save($tempFilePath)
    {
        if ($image = $this->cropAndResizePhoto($tempFilePath)) {
            $this->relativeFilePath = $this->createFilePath();

            return $this->fs->write($this->relativeFilePath, $image->encode());
        }

        return false;
    }

    /**
     * @return string
     */
    protected function createRandomFilename()
    {
        return time() . '-' . Str::random(32);
    }

    /**
     * @return string
     */
    public function createFilePath()
    {
        $relativePath = $this->getRelativePath();
        $filename     = $this->createRandomFilename();
        $extension    = 'jpg';

        return sprintf('%s/%s.%s', $relativePath, $filename, $extension);
    }

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
     * @param $imageProcessor
     */
    public function setImageProcessor($imageProcessor)
    {
        $this->imageProcessor = $imageProcessor;
    }

    /**
     * @param Filesystem $fs
     */
    public function setFilesystem(Filesystem $fs)
    {
        $this->fs = $fs;
    }

    /**
     * @return string
     */
    protected function getRelativePath()
    {
        return 'general';
    }

    /**
     * @return void
     */
    public function setRelativePath($path)
    {
        $this->relativeFilePath = $path;
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
        $publicBaseUrl = Yii::$app->params['public_storage_base_url'];

        return "{$publicBaseUrl}/{$this->getRelativeFilePath()}";
    }
}
