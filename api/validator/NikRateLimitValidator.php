<?php

namespace app\validator;

use Yii;
use yii\validators\Validator;

class NikRateLimitValidator extends Validator
{
    public function validateAttribute($model, $attribute)
    {
        $count = (new \yii\db\Query())
            ->from('beneficiaries_nik_logs')
            ->where(['user_id' => $model->user_id])
            ->andWhere(['>=', 'created_at', time() - 10])
            ->count();

        if ($count > 0) {
            $this->addError($model, $attribute, Yii::t('app', 'error.nik.limit'));
        }
    }
}
