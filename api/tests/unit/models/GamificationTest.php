<?php

namespace tests\unit\models;

use app\models\Gamification;
use Codeception\Test\Unit;

class GamificationTest extends Unit
{
    public function testTitleRequired()
    {
        $model = new Gamification();

        $model->validate();

        $this->assertTrue($model->hasErrors('title'));

        $model->title = '';

        $model->validate();

        $this->assertTrue($model->hasErrors('title'));

        $model->title = 'Ini adalah judul';

        $model->validate();

        $this->assertFalse($model->hasErrors('title'));
    }

    public function testTitleMinCharactersShouldFail()
    {
        $model = new Gamification();

        // allow min 10 chars
        $model->title = '123';

        $model->validate();

        $this->assertTrue($model->hasErrors('title'));
    }

    public function testTitleMinCharactersSuccess()
    {
        $model = new Gamification();

        // allow min 10 chars
        $model->title = '1234567890';

        $model->validate();

        $this->assertFalse($model->hasErrors('title'));
    }


    public function testTitleNotSafe()
    {
        $model = new Gamification();

        $model->title = '<script>alert()</script>';

        $model->validate();

        $this->assertTrue($model->hasErrors('title'));
    }

    public function testTotalHitRequired()
    {
        $model = new Gamification();

        $model->validate();

        $this->assertTrue($model->hasErrors('total_hit'));

        $model->total_hit = '';

        $model->validate();

        $this->assertTrue($model->hasErrors('total_hit'));

        $model->total_hit = 1;

        $model->validate();

        $this->assertFalse($model->hasErrors('total_hit'));
    }

    public function testTotalHitMustInteger()
    {
        $model = new Gamification();

        $model->total_hit = 1;

        $model->validate();

        $this->assertFalse($model->hasErrors('total_hit'));

        $model->total_hit = 'xxx';

        $model->validate();

        $this->assertTrue($model->hasErrors('total_hit'));
    }

    public function testStartDateRequired()
    {
        $model = new Gamification();

        $model->validate();

        $this->assertTrue($model->hasErrors('start_date'));

        $model->start_date = '';

        $model->validate();

        $this->assertTrue($model->hasErrors('start_date'));

        $model->start_date = '2019-06-01';

        $model->validate();

        $this->assertFalse($model->hasErrors('start_date'));
    }

    public function testStartDateValidValue()
    {
        $model = new Gamification();

        $model->start_date = '2019-06-01';

        $model->validate();

        $this->assertFalse($model->hasErrors('start_date'));
    }

    public function testStartDateInvalidStringValue()
    {
        $model = new Gamification();

        $model->start_date = 'xxx';

        $model->validate();

        $this->assertTrue($model->hasErrors('start_date'));
    }

    public function testStartDateInvalidIntegerValue()
    {
        $model = new Gamification();

        $model->start_date = 100;

        $model->validate();

        $this->assertTrue($model->hasErrors('start_date'));
    }

    public function testContentRequired()
    {
        $model = new Gamification();

        $model->validate();

        $this->assertTrue($model->hasErrors('description'));

        $model->description = '';

        $model->validate();

        $this->assertTrue($model->hasErrors('description'));

        $model->description = 'Test ini adalah description gamification';

        $model->validate();

        $this->assertFalse($model->hasErrors('description'));
    }

    public function testStatusRequired()
    {
        $model = new Gamification();

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
        $model = new Gamification();

        $model->status = 'OK';

        $model->validate();

        $this->assertTrue($model->hasErrors('status'));
    }

    public function testStatusInputInteger()
    {
        $model = new Gamification();

        $model->status = 0;

        $model->validate();

        $this->assertFalse($model->hasErrors('status'));
    }

    public function testStatusInputAllowedInteger()
    {
        $model = new Gamification();

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
