<?php

namespace tests\unit\models;

use app\models\NewsHoax;
use Codeception\Test\Unit;

class NewsHoaxTest extends Unit
{
    public function testTitleRequired()
    {
        $model = new NewsHoax();

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
        $model = new NewsHoax();

        // allow min 10 chars
        $model->title = '123456789';

        $model->validate();

        $this->assertTrue($model->hasErrors('title'));
    }

    public function testTitleMinCharactersSuccess()
    {
        $model = new NewsHoax();

        // allow min 10 chars
        $model->title = '1234567890';

        $model->validate();

        $this->assertFalse($model->hasErrors('title'));
    }

    public function testTitleMaxCharactersShouldFail()
    {
        $model = new NewsHoax();

        // max 100 chars
        // 101 chars should fail
        $model->title = 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean ma';

        $model->validate();

        $this->assertTrue($model->hasErrors('title'));
    }

    public function testTitleMaxCharactersSuccess()
    {
        $model = new NewsHoax();

        // max 100 chars
        // 100 chars should success
        $model->title = 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean m';

        $model->validate();

        $this->assertFalse($model->hasErrors('title'));
    }

    public function testCategoryRequired()
    {
        $model = new NewsHoax();

        $model->validate();

        $this->assertTrue($model->hasErrors('category_id'));

        $model->category_id = '';

        $model->validate();

        $this->assertTrue($model->hasErrors('category_id'));

        $model->category_id = 1;

        $model->validate();

        $this->assertFalse($model->hasErrors('category_id'));
    }

    public function testCategoryMustInteger()
    {
        $model = new NewsHoax();

        $model->category_id = 1;

        $model->validate();

        $this->assertFalse($model->hasErrors('category_id'));

        $model->category_id = 'xxx';

        $model->validate();

        $this->assertTrue($model->hasErrors('category_id'));
    }

    public function testUrlScheme()
    {
        $model = new NewsHoax();

        $model->source_url = 'test';

        $model->validate();

        $this->assertTrue($model->hasErrors('source_url'));

        $model->source_url = 'test.com';

        $model->validate();

        $this->assertTrue($model->hasErrors('source_url'));

        $model->source_url = 'http://google.com';

        $model->validate();

        $this->assertFalse($model->hasErrors('source_url'));
    }

    public function testSourceDateValidValue()
    {
        $model = new NewsHoax();

        $model->source_date = '2019-06-01';

        $model->validate();

        $this->assertFalse($model->hasErrors('source_date'));
    }

    public function testSourceDateInvalidStringValue()
    {
        $model = new NewsHoax();

        $model->source_date = 'xxx';

        $model->validate();

        $this->assertTrue($model->hasErrors('source_date'));
    }

    public function testSourceDateInvalidIntegerValue()
    {
        $model = new NewsHoax();

        $model->source_date = 100;

        $model->validate();

        $this->assertTrue($model->hasErrors('source_date'));
    }

    public function testSourceDateInvalidBooleanValue()
    {
        $model = new NewsHoax();

        $model->source_date = true;

        $model->validate();

        $this->assertTrue($model->hasErrors('source_date'));
    }

    public function testContentRequired()
    {
        $model = new NewsHoax();

        $model->validate();

        $this->assertTrue($model->hasErrors('content'));

        $model->content = '';

        $model->validate();

        $this->assertTrue($model->hasErrors('content'));

        $model->content = 'Test ini adalah content berita';

        $model->validate();

        $this->assertFalse($model->hasErrors('content'));
    }

    public function testContentLongCharactersShouldFail()
    {
        $model = new NewsHoax();

        $model->content = file_get_contents(__DIR__ . '/../../data/5572chars_html.txt');

        $model->validate();

        $this->assertFalse($model->hasErrors('content'));
    }

    public function testStatusRequired()
    {
        $model = new NewsHoax();

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
        $model = new NewsHoax();

        $model->status = 'OK';

        $model->validate();

        $this->assertTrue($model->hasErrors('status'));
    }

    public function testStatusInputInteger()
    {
        $model = new NewsHoax();

        $model->status = 0;

        $model->validate();

        $this->assertFalse($model->hasErrors('status'));
    }

    public function testStatusInputAllowedInteger()
    {
        $model = new NewsHoax();

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
