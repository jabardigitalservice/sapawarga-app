<?php

namespace app\validator;

use Carbon\Carbon;
use yii\validators\Validator;

class DatePastValidator extends Validator
{
    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;

        $date  = (new Carbon($value));
        $now   = Carbon::now();

        if ($date->greaterThanOrEqualTo($now)) {
            $this->addError($model, $attribute, 'Isian {attribute} tidak boleh melewati hari ini.');
        }
    }
}
