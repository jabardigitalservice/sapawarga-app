<?php

namespace Jdsteam\Sapawarga\Models\Concerns;

use Hashids\Hashids;
use app\models\UserMessage;

trait HasHashesId
{

    protected function getHashesId()
    {
        $hashids = new Hashids(\Yii::$app->params['hashidSaltSecret'], \Yii::$app->params['hashidLengthPad']);

        return $hashids->encode($this->id);
    }
}
