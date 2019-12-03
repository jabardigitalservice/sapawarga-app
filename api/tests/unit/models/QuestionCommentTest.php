<?php

namespace tests\unit\models;

use app\models\QuestionComment;

class QuestionCommentTest extends \Codeception\Test\Unit
{
    public function testValidateFillRequired()
    {
        $model = new QuestionComment();

        $this->assertFalse($model->validate());

        $this->assertTrue($model->hasErrors('text'));
        $this->assertTrue($model->hasErrors('status'));
    }

    public function testTextString()
    {
        $model = new QuestionComment();

        $model->text = false;
        $model->validate();
        $this->assertTrue($model->hasErrors('text'));

        $model->text = 123;
        $model->validate();
        $this->assertTrue($model->hasErrors('text'));

        // incorrect string pattern
        $model->text = 'abc';
        $model->validate();
        $this->assertFalse($model->hasErrors('text'));
    }

    public function testTextThousandCharactersShouldFail()
    {
        $model = new QuestionComment();

        $model->text = file_get_contents(__DIR__ . '/../../data/1000chars.txt');
        $model->validate();
        $this->assertTrue($model->hasErrors('text'));
    }

    public function testIsFlaggedBoolean()
    {
        $model = new QuestionComment();

        $model->is_flagged = 123;
        $model->validate();
        $this->assertTrue($model->hasErrors('is_flagged'));

        $model->is_flagged = 'abc';
        $model->validate();
        $this->assertTrue($model->hasErrors('is_flagged'));

        $model->is_flagged = true;
        $model->validate();
        $this->assertFalse($model->hasErrors('is_flagged'));
    }

    public function testStatusInteger()
    {
        $model = new QuestionComment();
        $model->status = 'OK';
        $model->validate();
        $this->assertTrue($model->hasErrors('status'));

        $model = new QuestionComment();
        $model->status = true;
        $model->validate();
        $this->assertTrue($model->hasErrors('status'));

        $model->status = 10;
        $model->validate();
        $this->assertFalse($model->hasErrors('status'));
    }

    public function testStatusInputAllowedInteger()
    {
        $model = new QuestionComment();

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
