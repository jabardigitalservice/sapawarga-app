<?php

namespace tests\unit\models;

use app\models\NewsImportant;
use Codeception\Test\Unit;

class NewsImportantTest extends Unit
{
    public function testTitleRequired()
    {
        $model = new NewsImportant();

        $model->validate();

        $this->assertTrue($model->hasErrors('title'));

        $model->title = '';

        $model->validate();

        $this->assertTrue($model->hasErrors('title'));

        $model->title = 'Ini adalah judul';

        $model->validate();

        $this->assertFalse($model->hasErrors('title'));
    }

    public function testContentRequired()
    {
        $model = new NewsImportant();

        $model->validate();

        $this->assertTrue($model->hasErrors('content'));

        $model->content = '';

        $model->validate();

        $this->assertTrue($model->hasErrors('content'));

        $model->content = 'Ini adalah judul';

        $model->validate();

        $this->assertFalse($model->hasErrors('content'));
    }

    public function testContentAllowHtml()
    {
        $model = new NewsImportant();

        $model->validate();

        $this->assertTrue($model->hasErrors('content'));

        $model->content = '';

        $model->validate();

        $this->assertTrue($model->hasErrors('content'));

        $model->content = 'Ini adalah judul';

        $model->validate();

        $this->assertFalse($model->hasErrors('content'));

        $model->content = '<p>Ini adalah judul dengan html</p>';

        $model->validate();

        $this->assertFalse($model->hasErrors('content'));
    }

    public function testTitleNotSafe()
    {
        $model = new NewsImportant();

        $model->title = '<script>alert()</script>';

        $model->validate();

        $this->assertTrue($model->hasErrors('title'));
    }

    public function testUrlScheme()
    {
        $model = new NewsImportant();

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

    public function testStatusRequired()
    {
        $model = new NewsImportant();

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
        $model = new NewsImportant();

        $model->status = 'OK';

        $model->validate();

        $this->assertTrue($model->hasErrors('status'));
    }

    public function testStatusInputInteger()
    {
        $model = new NewsImportant();

        $model->status = NewsImportant::STATUS_ACTIVE;

        $model->validate();

        $this->assertFalse($model->hasErrors('status'));
    }

    public function testStatusInputAllowedInteger()
    {
        $model = new NewsImportant();

        // Status = DELETED
        $model->status = -1;

        $model->validate();

        $this->assertFalse($model->hasErrors('status'));

        // Status = ACTIVE
        $model->status = NewsImportant::STATUS_ACTIVE;

        $model->validate();

        $this->assertFalse($model->hasErrors('status'));

        // Status = STARTED
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
