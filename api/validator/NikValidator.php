<?php

namespace app\validator;

use Yii;
use yii\validators\Validator;

class NikValidator extends Validator
{
    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;

        $allowedPrefix = [
            '11', '12', '13', '14', '15', '16', '17', '18', '19', '21',
            '31', '32', '33', '34', '35', '36', '51', '52', '53', '61',
            '62', '63', '64', '65', '71', '72', '73', '74', '75', '76',
            '81', '82', '91', '92',
        ];

        $prefix = substr($value, 0, 2);

        if (in_array($prefix, $allowedPrefix) && preg_match('/^[1-9]{1}[0-9]{11}[0-9]{3}[1-9]{1}$/', $value)) {
            return true;
        }

        $this->addError($model, $attribute, Yii::t('app', 'error.nik.invalid'));
    }
}
