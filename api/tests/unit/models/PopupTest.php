<?php

namespace tests\unit\models;

use app\models\Popup;
use Codeception\Test\Unit;

class PopupTest extends Unit
{
    public function testTitleRequired()
    {
        $model = new Popup();

        $model->validate();

        $this->assertTrue($model->hasErrors('title'));

        $model->title = '';

        $model->validate();

        $this->assertTrue($model->hasErrors('title'));

        $model->title = 'Ini adalah judul';

        $model->validate();

        $this->assertFalse($model->hasErrors('title'));
    }

    public function testTitleNotSafe()
    {
        $model = new Popup();

        $model->title = '<script>alert()</script>';

        $model->validate();

        $this->assertTrue($model->hasErrors('title'));
    }

    public function testInternalObjectTypeMustInRange()
    {
        $model = new Popup();

        $model->internal_object_type = 'news';

        $model->validate();

        $this->assertFalse($model->hasErrors('internal_object_type'));

        $model->internal_object_type = 111;

        $model->validate();

        $this->assertTrue($model->hasErrors('internal_object_type'));
    }

    public function testInternalObjectIdMustInteger()
    {
        $model = new Popup();

        $model->internal_object_id = 1;

        $model->validate();

        $this->assertFalse($model->hasErrors('internal_object_id'));

        $model->internal_object_id = 'xxx';

        $model->validate();

        $this->assertTrue($model->hasErrors('internal_object_id'));
    }

    public function testUrlScheme()
    {
        $model = new Popup();

        $model->link_url = 'test';

        $model->validate();

        $this->assertTrue($model->hasErrors('link_url'));

        $model->link_url = 'test.com';

        $model->validate();

        $this->assertTrue($model->hasErrors('link_url'));

        $model->link_url = 'http://google.com';

        $model->validate();

        $this->assertFalse($model->hasErrors('link_url'));
    }

    public function testTypeRequired()
    {
        $model = new Popup();

        $model->validate();

        $this->assertTrue($model->hasErrors('type'));

        $model->type = '';

        $model->validate();

        $this->assertTrue($model->hasErrors('type'));

        $model->type = 'external';
        $model->link_url = 'http://google.com';

        $model->validate();

        $this->assertFalse($model->hasErrors('type'));
    }


    public function testStartDateValidValue()
    {
        $model = new Popup();

        $model->start_date = '2019-06-01 00:00:00';
        $model->end_date   = '2019-09-01 00:00:00';

        $model->validate();

        $this->assertFalse($model->hasErrors('start_date'));
    }

    public function testStartDateInvalidStringValue()
    {
        $model = new Popup();

        $model->start_date = 'xxx';

        $model->validate();

        $this->assertTrue($model->hasErrors('start_date'));
    }

    public function testStartDateInvalidIntegerValue()
    {
        $model = new Popup();

        $model->start_date = 100;

        $model->validate();

        $this->assertTrue($model->hasErrors('start_date'));
    }

    public function testStartDateInvalidBooleanValue()
    {
        $model = new Popup();

        $model->start_date = true;

        $model->validate();

        $this->assertTrue($model->hasErrors('start_date'));
    }

    public function testEndDateValidValue()
    {
        $model = new Popup();

        $model->start_date = '2019-06-01 00:00:00';
        $model->end_date   = '2019-09-01 00:00:00';

        $model->validate();

        $this->assertFalse($model->hasErrors('end_date'));
    }

    public function testEndDateInvalidStringValue()
    {
        $model = new Popup();

        $model->end_date = 'xxx';

        $model->validate();

        $this->assertTrue($model->hasErrors('end_date'));
    }

    public function testEndDateInvalidIntegerValue()
    {
        $model = new Popup();

        $model->end_date = 100;

        $model->validate();

        $this->assertTrue($model->hasErrors('end_date'));
    }

    public function testEndDateInvalidBooleanValue()
    {
        $model = new Popup();

        $model->end_date = true;

        $model->validate();

        $this->assertTrue($model->hasErrors('end_date'));
    }

    public function testEndDateAfterStartDate()
    {
        $model = new Popup();

        $model->start_date = '2019-06-01 00:00:00';
        $model->end_date   = '2019-09-01 00:00:00';

        $model->validate();

        $this->assertFalse($model->hasErrors('end_date'));
    }

    public function testEndDateBeforeStartDate()
    {
        $model = new Popup();

        $model->start_date = '2019-09-01 00:00:00';
        $model->end_date   = '2019-06-01 00:00:00';

        $model->validate();

        $this->assertTrue($model->hasErrors('start_date'));
    }

    public function testStatusRequired()
    {
        $model = new Popup();

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
        $model = new Popup();

        $model->status = 'OK';

        $model->validate();

        $this->assertTrue($model->hasErrors('status'));
    }

    public function testStatusInputInteger()
    {
        $model = new Popup();

        $model->status = Popup::STATUS_ACTIVE;

        $model->validate();

        $this->assertFalse($model->hasErrors('status'));
    }

    public function testStatusInputAllowedInteger()
    {
        $model = new Popup();

        // Status = DELETED
        $model->status = Popup::STATUS_DELETED;

        $model->validate();

        $this->assertFalse($model->hasErrors('status'));

        // Status = ACTIVE
        $model->status = Popup::STATUS_ACTIVE;

        $model->validate();

        $this->assertFalse($model->hasErrors('status'));

        // Status = STARTED
        $model->status = Popup::STATUS_STARTED;

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
