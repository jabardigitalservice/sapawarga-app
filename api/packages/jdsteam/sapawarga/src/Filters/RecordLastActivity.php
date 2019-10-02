<?php

namespace Jdsteam\Sapawarga\Filters;

use app\models\User;
use Yii;
use yii\base\ActionFilter;
use yii\db\Expression;

class RecordLastActivity extends ActionFilter
{
    public function afterAction($action, $result)
    {
        $userId = Yii::$app->user->getId();

        if ($userId) {
            $user              = User::findIdentity($userId);
            $user->last_access = new Expression('NOW()');
            $user->save(false);
        }

        return parent::afterAction($action, $result);
    }
}
