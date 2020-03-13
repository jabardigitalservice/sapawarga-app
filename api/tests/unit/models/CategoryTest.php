<?php

namespace tests\unit\models;

use app\models\Category;

class CategoryTest extends \Codeception\Test\Unit
{
    public function testValidateFillRequired()
    {
        $model = new Category();

        $this->assertFalse($model->validate());

        $this->assertTrue($model->hasErrors('type'));
        $this->assertTrue($model->hasErrors('name'));
        $this->assertTrue($model->hasErrors('status'));
        $this->assertFalse($model->hasErrors('meta'));
    }

    public function testNameMaxCharactersValid()
    {
        $model       = new Category();
        $model->name = 'My Name';

        $model->validate();

        $this->assertFalse($model->hasErrors('name'));

        $model->name = 'klARBlYBSY2wqkyuIz3t1A8AXUQLTDX1Ij7raxZ89r9H97hFLOCbi36BpCIr3yi5';

        $model->validate();

        $this->assertFalse($model->hasErrors('name'));
    }

    public function testNameTooLong()
    {
        $model       = new Category();
        $model->name = 'klARBlYBSY2wqkyuIz3t1A8AXUQLTDX1Ij7raxZ89r9H97hFLOCbi36BpCIr3yi5xxxxx';

        $model->validate();

        $this->assertTrue($model->hasErrors('name'));
    }

    public function testNameNotSafe()
    {
        $model       = new Category();
        $model->name = '<script>alert()</script>';

        $model->validate();

        // $this->assertTrue($model->hasErrors('name'));
    }


    public function testTypeTooLong()
    {
        $model       = new Category();
        $model->type = 'klARBlYBSY2wqkyuIz3t1A8AXUQLTDX1Ij7raxZ89r9H97hFLOCbi36BpCIr3yi5xxxxx';

        $model->validate();

        $this->assertTrue($model->hasErrors('type'));
    }

    public function testDefaultCategory()
    {
        $model       = new Category();
        $model->type = 'type';
        $model->name = 'New Category';
        $model->status = Category::STATUS_ACTIVE;

        $model->validate();
        $this->assertTrue($model->hasErrors('type'));

        $model2       = new Category();
        $model2->type = 'type';
        $model2->name = 'Lainnya';
        $model2->status = Category::STATUS_ACTIVE;

        $model2->validate();
        $this->assertFalse($model2->hasErrors('type'));
    }
}
