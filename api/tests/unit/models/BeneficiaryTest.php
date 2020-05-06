<?php

namespace tests\unit\models;

use app\models\Beneficiary;

class BeneficiaryTest extends \Codeception\Test\Unit
{
    public function testValidateFillRequired()
    {
        $model = new Beneficiary();

        $this->assertFalse($model->validate());

        $this->assertTrue($model->hasErrors('name'));
        $this->assertTrue($model->hasErrors('status_verification'));
        $this->assertTrue($model->hasErrors('status'));
    }

    public function testDomicileRWRT()
    {
        // domicile_rw
        $model = new Beneficiary();
        $model->domicile_rw = null;
        $model->validate();
        $this->assertFalse($model->hasErrors('domicile_rw'));

        $model->domicile_rw = ' 1 ';
        $model->validate();
        $this->assertEquals('1', $model->domicile_rw);

        $model->domicile_rw = '001';
        $model->validate();
        $this->assertEquals('1', $model->domicile_rw);

        $model->domicile_rw = 1;
        $model->validate();
        $this->assertEquals('1', $model->domicile_rw);

        // domicile_rt
        $model = new Beneficiary();
        $model->domicile_rt = null;
        $model->validate();
        $this->assertFalse($model->hasErrors('domicile_rt'));

        $model->domicile_rt = ' 1 ';
        $model->validate();
        $this->assertEquals('1', $model->domicile_rt);

        $model->domicile_rt = '001';
        $model->validate();
        $this->assertEquals('1', $model->domicile_rt);

        $model->domicile_rt = 1;
        $model->validate();
        $this->assertEquals('1', $model->domicile_rt);
    }
}
