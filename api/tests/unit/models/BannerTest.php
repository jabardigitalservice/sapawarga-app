<?php

namespace tests\unit\models;

use app\models\Banner;
use Codeception\Test\Unit;

class BannerTest extends Unit
{
    public function testTitleRequired()
    {
        $model = new Banner();

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
        $model = new Banner();

        $model->title = '<script>alert()</script>';

        $model->validate();

        $this->assertTrue($model->hasErrors('title'));
    }

    public function testInternalCategoryIdMustInRange()
    {
        $model = new Banner();

        $model->internal_category = 'news';

        $model->validate();

        $this->assertFalse($model->hasErrors('internal_category'));

        $model->internal_category = 111;

        $model->validate();

        $this->assertTrue($model->hasErrors('internal_category'));
    }

    public function testInternalEntityIdMustInteger()
    {
        $model = new Banner();

        $model->internal_entity_id = 1;

        $model->validate();

        $this->assertFalse($model->hasErrors('internal_entity_id'));

        $model->internal_entity_id = 'xxx';

        $model->validate();

        $this->assertTrue($model->hasErrors('internal_entity_id'));
    }

    public function testUrlScheme()
    {
        $model = new Banner();

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

    public function testTypeExternalRequired()
    {
        $model = new Banner();

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

    public function testTypeInternalRequired()
    {
        $model = new Banner();
        $model->type = 'internal';

        $model->validate();

        $this->assertTrue($model->hasErrors('type'));

        $model->internal_category = 'news';
        $model->internal_entity_id = 1;

        $model->validate();

        $this->assertFalse($model->hasErrors('type'));
    }

    public function testStatusRequired()
    {
        $model = new Banner();

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
        $model = new Banner();

        $model->status = 'OK';

        $model->validate();

        $this->assertTrue($model->hasErrors('status'));
    }

    public function testStatusInputInteger()
    {
        $model = new Banner();

        $model->status = 0;

        $model->validate();

        $this->assertFalse($model->hasErrors('status'));
    }

    public function testStatusInputAllowedInteger()
    {
        $model = new Banner();

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
