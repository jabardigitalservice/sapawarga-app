<?php

namespace tests\unit\models;

use app\models\Release;

class ReleaseTest extends \Codeception\Test\Unit
{
    public function testValidateFillRequired()
    {
        $model = new Release();

        $this->assertFalse($model->validate());

        $this->assertTrue($model->hasErrors('version'));
        $this->assertTrue($model->hasErrors('force_update'));
    }

    public function testVersionString()
    {
        $model = new Release();

        $model->version = false;
        $model->validate();
        $this->assertTrue($model->hasErrors('version'));

        $model->version = 123;
        $model->validate();
        $this->assertTrue($model->hasErrors('version'));

        // incorrect string pattern
        $model->version = 'abc';
        $model->validate();
        $this->assertTrue($model->hasErrors('version'));

        // correct string pattern
        $model->version = '1.0.0';
        $model->validate();
        $this->assertFalse($model->hasErrors('name'));
    }

    public function testForceUpdateBoolean()
    {
        $model = new Release();

        $model->force_update = 123;
        $model->validate();
        $this->assertTrue($model->hasErrors('force_update'));

        $model->force_update = 'abc';
        $model->validate();
        $this->assertTrue($model->hasErrors('force_update'));

        $model->force_update = true;
        $model->validate();
        $this->assertFalse($model->hasErrors('force_update'));
    }
}
