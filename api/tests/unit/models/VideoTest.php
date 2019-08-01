<?php

namespace tests\unit\models;

use app\models\Video;

class VideoTest extends \Codeception\Test\Unit
{
    public function testValidateRequired()
    {
        $model = new Video();

        $model->validate();

        // Mandatory
        $this->assertTrue($model->hasErrors('title'));
        $this->assertTrue($model->hasErrors('category_id'));
        $this->assertTrue($model->hasErrors('source'));
        $this->assertTrue($model->hasErrors('video_url'));
        $this->assertTrue($model->hasErrors('status'));
    }

    public function testTitleValid()
    {
        $model = new Video();

        $model->title = 'Ini adalah judul';

        $model->validate();

        $this->assertFalse($model->hasErrors('title'));
    }

    public function testTitleNotEmpty()
    {
        $model = new Video();

        $model->title = '';

        $model->validate();

        $this->assertTrue($model->hasErrors('title'));
    }

    public function testTitleTooLong()
    {
        $model = new Video();

        $model->title = '9QDdyAqPd35eG06wTaaHilQIk2pEuoftrIBy5FNKdUUwMcyNMl1i3ObgeX9Qome73njU2iQtif8muLml
                2VMPfbkrf2OLsL4wBkvF692wZ7CrkfsaZ6kDswGtFC0Bp2Bb3kL1VnRsrJm7X9AKg8k3gMeLtdeQcqFSyb7q
                ydwBdmRUOSOYgwJLdDtheV7J4cSBYL8p7TmXhr57Vyg7zi2ewTEQ4XLVql3HJmHMXTqyQjWJKktycZNznK0uZ
                lG5FNqAfOZjnyvZW4fityhY9Wf0DPYFro4mapRcLVtWiAqXYIGX';

        $model->validate();

        $this->assertTrue($model->hasErrors('title'));
    }

    public function testTitleMinCharacters()
    {
        $model = new Video();

        $model->title = 'Coba';

        $model->validate();

        $this->assertTrue($model->hasErrors('title'));
    }

    public function testTitleMaxCharactersShouldFail()
    {
        $model = new Video();

        // 101 chars should fail
        $model->title = 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean ma';

        $model->validate();

        $this->assertTrue($model->hasErrors('title'));
    }

    public function testTitleMaxCharactersSuccess()
    {
        $model = new Video();

        // 100 chars should success
        $model->title = 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean m';

        $model->validate();

        $this->assertFalse($model->hasErrors('title'));
    }

    public function testVideoYoutubeUrlShouldFail()
    {
        $model = new Video();

        $model->video_url = 'https://www.google.com/watch?v=iE3az5S27Wk';

        $model->validate();

        $this->assertTrue($model->hasErrors('video_url'));
    }

    public function testVideoYoutubeUrlSuccess()
    {
        $model = new Video();

        $model->video_url = 'https://www.youtube.com/watch?v=iE3az5S27Wk';

        $model->validate();

        $this->assertFalse($model->hasErrors('video_url'));
    }

    public function testTitleNotSafe()
    {
        $model = new Video();

        $model->title = '<script>alert()</script>';

        $model->validate();

        $this->assertTrue($model->hasErrors('title'));
    }

    public function testSourceValid()
    {
        $model = new Video();

        $model->source = 'youtube';

        $model->validate();

        $this->assertFalse($model->hasErrors('source'));
    }

    public function testSourceNotEmpty()
    {
        $model = new Video();

        $model->source = '';

        $model->validate();

        $this->assertTrue($model->hasErrors('source'));
    }

    public function testAreaMustInteger()
    {
        $model = new Video();

        $model->kabkota_id = 'test';

        $model->validate();

        $this->assertTrue($model->hasErrors('kabkota_id'));

        $model->kabkota_id = 1;

        $model->validate();

        $this->assertFalse($model->hasErrors('kabkota_id'));
    }

    public function testCategoryIdMustInteger()
    {
        $model = new Video();

        $model->category_id = 'test';

        $model->validate();

        $this->assertTrue($model->hasErrors('category_id'));

        $model->category_id = 1;

        $model->validate();

        $this->assertFalse($model->hasErrors('category_id'));
    }

    public function testStatusInputString()
    {
        $model = new Video();

        $model->status = 'OK';

        $model->validate();

        $this->assertTrue($model->hasErrors('status'));
    }

    public function testCreateScenarioStaff()
    {
        $model         = new Video();
        $model->status = 10;

        $model->validate();

        $this->assertFalse($model->hasErrors('status'));
    }
}
