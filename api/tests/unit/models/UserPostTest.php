<?php

namespace tests\unit\models;

use app\models\UserPost;
use Codeception\Test\Unit;

class UserPostTest extends Unit
{
    public function testTitleRequired()
    {
        $model = new UserPost();
        $model->validate();
        $this->assertTrue($model->hasErrors('text'));

        $model->text = '';
        $model->validate();
        $this->assertTrue($model->hasErrors('text'));

        $model->text = 'Ini adalah judul';
        $model->validate();
        $this->assertFalse($model->hasErrors('text'));
    }

    public function testTitleMinCharactersShouldFail()
    {
        $model = new UserPost();
        $model->text = '123'; // allow min 10 chars
        $model->validate();
        $this->assertTrue($model->hasErrors('text'));
    }

    public function testTitleMinCharactersSuccess()
    {
        $model = new UserPost();
        $model->text = '1234567890'; // allow min 10 chars
        $model->validate();
        $this->assertFalse($model->hasErrors('text'));
    }

    public function testStatusRequired()
    {
        $model = new UserPost();
        $model->validate();
        $this->assertTrue($model->hasErrors('status'));

        $model->status = '';
        $model->validate();
        $this->assertTrue($model->hasErrors('status'));

        $model->status = 10;
        $model->validate();
        $this->assertFalse($model->hasErrors('status'));
    }

    public function testStatusInputString()
    {
        $model = new UserPost();
        $model->status = 'OK';
        $model->validate();
        $this->assertTrue($model->hasErrors('status'));
    }

    public function testStatusInputInteger()
    {
        $model = new UserPost();
        $model->status = 0;
        $model->validate();
        $this->assertFalse($model->hasErrors('status'));
    }

    public function testTagsPathInput()
    {
        $model = new UserPost();

        // false
        $model->tags = 'bencana, siskamling, gotongroyong, pengajian';
        $model->validate();
        $this->assertFalse($model->hasErrors('tags'));
    }

    public function testImageInput()
    {
        $model = new UserPost();

        // false
        $model->images = 'firman';
        $model->validate();
        $this->assertFalse($model->hasErrors('images'));
    }

    public function testImageInputTrue()
    {
        $model = new UserPost();

        // false
        $model->images = '[{"path": "general/1580275858-wsYjnF0Z4FUzWnJYIFpO43P87kTssxHc.jpg"}]';
        $model->validate();
        $this->assertFalse($model->hasErrors('images'));
    }

    public function testAnswerIdInputAllowedInteger()
    {
        $model = new UserPost();

        // String
        $model->last_user_post_comment_id = 'firman';
        $model->validate();
        $this->assertTrue($model->hasErrors('last_user_post_comment_id'));

        // Integer
        $model->last_user_post_comment_id = 2;
        $model->validate();
        $this->assertFalse($model->hasErrors('last_user_post_comment_id'));

        $model->last_user_post_comment_id = 5;
        $model->validate();
        $this->assertFalse($model->hasErrors('last_user_post_comment_id'));
    }

    public function testStatusInputAllowedInteger()
    {
        $model = new UserPost();

        // Status = DELETED
        $model->status = -1;
        $model->validate();
        $this->assertFalse($model->hasErrors('status'));

        // Status = DISABLED
        $model->status = 0;
        $model->validate();
        $this->assertFalse($model->hasErrors('status'));

        // Status = ACTIVE
        $model->status = 10;
        $model->validate();
        $this->assertFalse($model->hasErrors('status'));

        // Status, Random
        $model->status = 2;
        $model->validate();
        $this->assertTrue($model->hasErrors('status'));

        // Status, Random
        $model->status = 5;
        $model->validate();
        $this->assertTrue($model->hasErrors('status'));
    }
}
