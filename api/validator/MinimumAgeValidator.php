<?php

namespace app\validator;

use Carbon\Carbon;
use yii\validators\Validator;

class MinimumAgeValidator extends Validator
{
    public function validateAttribute($model, $attribute)
    {
        $value = $model->$attribute;

        $date  = (new Carbon($value));
        $limit = Carbon::now();

        if ($date->diffInYears($limit) < 10) {
            $this->addError($model, $attribute, 'Usia minimal 10 tahun.');
        }
    }
}
