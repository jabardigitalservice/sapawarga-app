<?php

namespace tests\unit\models;

use app\models\Question;
use Codeception\Test\Unit;

class QuestionTest extends Unit
{
    public function testTitleRequired()
    {
        $model = new Question();
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
        $model = new Question();
        $model->text = '123'; // allow min 10 chars
        $model->validate();
        $this->assertTrue($model->hasErrors('text'));
    }

    public function testTitleMinCharactersSuccess()
    {
        $model = new Question();
        $model->text = '1234567890'; // allow min 10 chars
        $model->validate();
        $this->assertFalse($model->hasErrors('text'));
    }

    public function testStatusRequired()
    {
        $model = new Question();
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
        $model = new Question();
        $model->status = 'OK';
        $model->validate();
        $this->assertTrue($model->hasErrors('status'));
    }

    public function testStatusInputInteger()
    {
        $model = new Question();
        $model->status = 0;
        $model->validate();
        $this->assertFalse($model->hasErrors('status'));
    }

    public function testIsFlaggedIdInputAllowedInteger()
    {
        $model = new Question();

        // true
        $model->is_flagged = 0;
        $model->validate();
        $this->assertFalse($model->hasErrors('is_flagged'));

        // false
        $model->is_flagged = 1;
        $model->validate();
        $this->assertFalse($model->hasErrors('is_flagged'));

        // false
        $model->is_flagged = 'firman';
        $model->validate();
        $this->assertTrue($model->hasErrors('is_flagged'));
    }

    public function testAnswerIdInputAllowedInteger()
    {
        $model = new Question();

        // String
        $model->answer_id = 'firman';
        $model->validate();
        $this->assertTrue($model->hasErrors('answer_id'));

        // Integer
        $model->answer_id = 2;
        $model->validate();
        $this->assertFalse($model->hasErrors('answer_id'));

        $model->answer_id = 5;
        $model->validate();
        $this->assertFalse($model->hasErrors('answer_id'));
    }

    public function testStatusInputAllowedInteger()
    {
        $model = new Question();

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
