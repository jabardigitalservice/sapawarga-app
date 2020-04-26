<?php

namespace app\validator;

use Yii;
use yii\validators\Validator;

class NikValidator extends Validator
{
    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;

        if (!preg_match('/^[0-9]{16}$/', $value) ||
            preg_match('/^[0-9]{12}[0]{4}$/', $value) ||
            preg_match('/^0[0-9]{15}$/', $value)
        ) {
            $this->addError($model, $attribute, Yii::t('app', 'error.nik.invalid'));
        }
    }
}
