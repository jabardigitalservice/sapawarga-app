<?php

namespace tests\unit\models;

use app\models\NewsImportantComment;

class NewsImportantCommentTest extends \Codeception\Test\Unit
{
    public function testValidateFillRequired()
    {
        $model = new NewsImportantComment();

        $this->assertFalse($model->validate());

        $this->assertTrue($model->hasErrors('text'));
        $this->assertTrue($model->hasErrors('status'));
    }

    public function testTextString()
    {
        $model = new NewsImportantComment();

        $model->text = false;
        $model->validate();
        $this->assertTrue($model->hasErrors('text'));

        $model->text = 123;
        $model->validate();
        $this->assertTrue($model->hasErrors('text'));

        $model->text = 'abc';
        $model->validate();
        $this->assertFalse($model->hasErrors('text'));
    }

    public function testTextThousandCharactersShouldFail()
    {
        $model = new NewsImportantComment();

        $model->text = file_get_contents(__DIR__ . '/../../data/1000chars.txt');
        $model->validate();
        $this->assertTrue($model->hasErrors('text'));
    }

    public function testStatusInteger()
    {
        $model = new NewsImportantComment();
        $model->status = 'OK';
        $model->validate();
        $this->assertTrue($model->hasErrors('status'));

        $model = new NewsImportantComment();
        $model->status = true;
        $model->validate();
        $this->assertTrue($model->hasErrors('status'));

        $model->status = 10;
        $model->validate();
        $this->assertFalse($model->hasErrors('status'));
    }

    public function testStatusInputAllowedInteger()
    {
        $model = new NewsImportantComment();

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
    }
}
