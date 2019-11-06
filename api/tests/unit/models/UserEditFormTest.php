<?php

namespace tests\unit\models;

use app\models\UserEditForm;
use Carbon\Carbon;
use Codeception\Test\Unit;

class UserEditFormTest extends Unit
{
    public function testBirthdateShouldNotFuture()
    {
        $model             = new UserEditForm();
        $model->birth_date = (new Carbon())->addDay()->toDateString();

        $this->assertFalse($model->validate());

        $this->assertTrue($model->hasErrors('birth_date'));
    }

    public function testBirthdateMinimumAge()
    {
        $model             = new UserEditForm();
        $model->birth_date = (new Carbon())->subYear()->toDateString();

        $this->assertFalse($model->validate());

        $this->assertTrue($model->hasErrors('birth_date'));
    }

    public function testBirthdateAllowedAge()
    {
        $model             = new UserEditForm();
        $model->birth_date = (new Carbon())->subYears(10)->toDateString();

        $model->validate();

        $this->assertFalse($model->hasErrors('birth_date'));
    }
}
