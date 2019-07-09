<?php

namespace tests\unit\models;

use app\models\NewsChannel;

class NewsChannelTest extends \Codeception\Test\Unit
{
    public function testValidateFillRequired()
    {
        $model = new NewsChannel();

        $this->assertFalse($model->validate());

        $this->assertTrue($model->hasErrors('name'));
        $this->assertTrue($model->hasErrors('status'));
    }

    public function testNameMaxCharactersValid()
    {
        $model       = new NewsChannel();
        $model->name = 'My Name';

        $model->validate();

        $this->assertFalse($model->hasErrors('name'));

        $model->name = '6h03Tr0NROAOJaXr9pWsiS5ICrO6mr60J6jCg5ZADhwk8QDNb1InlCpjsKfDYGKmYdAy8CTldO3V2vYSkgZ780w7JZ5pjICwcc7i';

        $model->validate();

        $this->assertFalse($model->hasErrors('name'));
    }

    public function testNameTooLong()
    {
        $model       = new NewsChannel();
        $model->name = '6h03Tr0NROAOJaXr9pWsiS5ICrO6mr60J6jCg5ZADhwk8QDNb1InlCpjsKfDYGKmYdAy8CTldO3V2vYSkgZ780w7JZ5pjICwcc7i7';

        $model->validate();

        $this->assertTrue($model->hasErrors('name'));
    }

    public function testStatusInputInvalid()
    {
        $model = new NewsChannel();

        $model->status = 'OK';
        $model->validate();

        $this->assertTrue($model->hasErrors('status'));

        $model->status = ['key' => 'value'];
        $model->validate();

        $this->assertTrue($model->hasErrors('status'));

        $model->status = '';
        $model->validate();

        $this->assertTrue($model->hasErrors('status'));
    }

    public function testStatusInputValid()
    {
        $model = new NewsChannel();

        $model->status = 10;
        $model->validate();

        $this->assertFalse($model->hasErrors('status'));
    }
}
