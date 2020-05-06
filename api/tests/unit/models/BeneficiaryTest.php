<?php

namespace tests\unit\models;

use app\models\Beneficiary;
use app\validator\NikValidator;
use yii\base\DynamicModel;

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

    public function testNIK()
    {
        // use core validation rules
        $model = new Beneficiary();
        $model->nik = null;
        $model->validate();
        $this->assertFalse($model->hasErrors('nik'));

        $model->nik = ' 3211110101800001 ';
        $model->validate();
        $this->assertEquals('3211110101800001', $model->nik);

        // use NikValidator
        $model = new DynamicModel(['nik' => null]);
        $model->addRule('nik', NikValidator::class);

        $model->nik = '0000000101800001';
        $model->validate();
        $this->assertTrue($model->hasErrors('nik'));

        $model->nik = '3200000101800000';
        $model->validate();
        $this->assertTrue($model->hasErrors('nik'));

        $model->nik = '3200000101800001';
        $model->validate();
        $this->assertFalse($model->hasErrors('nik'));
    }

    public function testFamilyMembers()
    {
        $model = new Beneficiary();
        $model->total_family_members = 'asd';
        $model->validate();
        $this->assertTrue($model->hasErrors('total_family_members'));

        $model->total_family_members = '0 1';
        $model->validate();
        $this->assertTrue($model->hasErrors('total_family_members'));

        $model->total_family_members = null;
        $model->validate();
        $this->assertFalse($model->hasErrors('total_family_members'));

        $model->total_family_members = '1';
        $model->validate();
        $this->assertFalse($model->hasErrors('total_family_members'));

        $model->total_family_members = 1;
        $model->validate();
        $this->assertFalse($model->hasErrors('total_family_members'));
    }

    public function testIsPoorNewIsNeedHelp()
    {
        $model = new Beneficiary();
        $model->is_poor_new = 'asd';
        $model->is_need_help = '0 1';
        $model->validate();
        $this->assertTrue($model->hasErrors('is_poor_new'));
        $this->assertTrue($model->hasErrors('is_need_help'));

        $model = new Beneficiary();
        $model->is_poor_new = 2;
        $model->is_need_help = '01';
        $model->validate();
        $this->assertTrue($model->hasErrors('is_poor_new'));
        $this->assertFalse($model->hasErrors('is_need_help'));

        $model->is_poor_new = null;
        $model->is_need_help = null;
        $model->validate();
        $this->assertFalse($model->hasErrors('is_poor_new'));
        $this->assertFalse($model->hasErrors('is_need_help'));

        $model->is_poor_new = 0;
        $model->is_need_help = '1';
        $model->validate();
        $this->assertFalse($model->hasErrors('is_poor_new'));
        $this->assertFalse($model->hasErrors('is_need_help'));
    }

    public function testStatusVerification()
    {
        $model = new Beneficiary();
        $model->status_verification = null;
        $model->validate();
        $this->assertTrue($model->hasErrors('status_verification'));

        $model->status_verification = '';
        $model->validate();
        $this->assertTrue($model->hasErrors('status_verification'));

        $model->status_verification = 4;
        $model->validate();
        $this->assertTrue($model->hasErrors('status_verification'));

        $model->status_verification = '1';
        $model->validate();
        $this->assertFalse($model->hasErrors('status_verification'));

        $model->status_verification = 1;
        $model->validate();
        $this->assertFalse($model->hasErrors('status_verification'));
    }
}
