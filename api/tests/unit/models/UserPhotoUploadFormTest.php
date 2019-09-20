<?php

namespace tests\models;

use app\models\User;
use app\models\UserPhotoUploadForm;
use Intervention\Image\Gd\Driver;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use yii\web\UploadedFile;
use Mockery as m;
use yii2tech\filestorage\local\Bucket;

class UserPhotoUploadFormTest extends \Codeception\Test\Unit
{
    public function testValidateRequired()
    {
        $model = new UserPhotoUploadForm();

        $this->assertFalse($model->validate());

        $this->assertTrue($model->hasErrors('file'));
    }

    public function testValidateSuccess()
    {
        $_FILES = [
            'image' => [
                'tmp_name' => __DIR__ . '/../../data/example.jpg',
                'name'     => 'example.jpg',
                'type'     => 'image/jpeg ',
                'size'     => 47152,
                'error'    => 0,
            ],
        ];

        $model        = new UserPhotoUploadForm();
        $model->file  = UploadedFile::getInstanceByName('image');
        $model->type  = $model;

        $this->assertTrue($model->validate());
    }

    public function testValidateInvalidFileType()
    {
        $_FILES = [
            'image' => [
                'tmp_name' => __DIR__ . '/../../data/example.txt',
                'name'     => 'example.txt',
                'type'     => 'text/plain',
                'size'     => 2605,
                'error'    => 0,
            ],
        ];

        $model        = new UserPhotoUploadForm();
        $model->file  = UploadedFile::getInstanceByName('image');

        $this->assertFalse($model->validate());

        $this->assertTrue($model->hasErrors('file'));
    }

    public function testValidateFileTooBig()
    {
        $_FILES = [
            'image' => [
                'tmp_name' => __DIR__ . '/../../data/example.jpg',
                'name'     => 'example.jpg',
                'type'     => 'image/jpeg ',
                'size'     => 1024 * 1024 * 10, // override
                'error'    => 0,
            ],
        ];

        $model        = new UserPhotoUploadForm();
        $model->file  = UploadedFile::getInstanceByName('image');

        $this->assertFalse($model->validate());

        $this->assertTrue($model->hasErrors('file'));
    }

    public function testCropAndResize()
    {
        $tempFilePath = '/tmp/test.jpg'; // mock file path

        $imageProcessor = m::mock(ImageManager::class);
        $imageProcessor->shouldReceive('make')->once()->andReturnUsing(function () {
            $driver = new Driver();
            $core = imagecreatetruecolor(1024, 1024);

            $image = new Image($driver, $core);

            return $image;
        });

        $model = new UserPhotoUploadForm();
        $model->setImageProcessor($imageProcessor);

        $image = $model->cropAndResizePhoto($tempFilePath);

        $this->assertEquals($image->getHeight(), 640);
        $this->assertEquals($image->getWidth(), 640);
    }

    public function testCropAndResizeSmallImage()
    {
        $tempFilePath = '/tmp/test.jpg'; // mock file path

        $imageProcessor = m::mock(ImageManager::class);
        $imageProcessor->shouldReceive('make')->once()->andReturnUsing(function () {
            $driver = new Driver();
            $core = imagecreatetruecolor(128, 128);

            $image = new Image($driver, $core);

            return $image;
        });

        $model = new UserPhotoUploadForm();
        $model->setImageProcessor($imageProcessor);

        $image = $model->cropAndResizePhoto($tempFilePath);

        $this->assertEquals($image->getHeight(), 640);
        $this->assertEquals($image->getWidth(), 640);
    }
}
