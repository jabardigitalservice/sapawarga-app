<?php

namespace Jdsteam\Sapawarga\Filters;

use app\components\LogHelper;
use app\models\User;
use Monolog\Logger;
use Yii;
use yii\base\ActionFilter;
use yii\db\Expression;

class RecordLastActivity extends ActionFilter
{
    public function afterAction($action, $result)
    {
        $userId = Yii::$app->user->getId();

        if ($userId) {
            $user                 = User::findIdentity($userId);
            $user->last_access_at = new Expression('NOW()');
            $user->save(false);
        }

        LogHelper::logEventByUser('USER_LAST_ACTIVITY');

        return parent::afterAction($action, $result);
    }
}
