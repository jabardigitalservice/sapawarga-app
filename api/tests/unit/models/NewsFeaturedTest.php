<?php

namespace tests\unit\models;

use app\models\NewsFeatured;
use Codeception\Test\Unit;

class NewsFeaturedTest extends Unit
{
    public function testValidateShouldRequired()
    {
        $model = new NewsFeatured();

        $model->validate();

        $this->assertTrue($model->hasErrors('news_id'));
        $this->assertTrue($model->hasErrors('seq'));
        $this->assertFalse($model->hasErrors('kabkota_id'));
    }

    public function testValidateNewsIdShouldInteger()
    {
        $model = new NewsFeatured();
        $model->news_id = 1;
        $model->validate();

        $this->assertFalse($model->hasErrors('news_id'));

        $model = new NewsFeatured();
        $model->news_id = 'ok';
        $model->validate();

        $this->assertTrue($model->hasErrors('news_id'));
    }

    public function testValidateSequenceShouldInteger()
    {
        $model = new NewsFeatured();
        $model->seq = 1;
        $model->validate();

        $this->assertFalse($model->hasErrors('seq'));

        $model = new NewsFeatured();
        $model->seq = 'ok';
        $model->validate();

        $this->assertTrue($model->hasErrors('seq'));
    }

    public function testValidateKabkotaIdShouldInteger()
    {
        $model = new NewsFeatured();
        $model->kabkota_id = 1;
        $model->validate();

        $this->assertFalse($model->hasErrors('kabkota_id'));

        $model = new NewsFeatured();
        $model->kabkota_id = 'ok';
        $model->validate();

        $this->assertTrue($model->hasErrors('kabkota_id'));
    }
}
