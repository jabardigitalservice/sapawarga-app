<?php

namespace app\components;

use Yii;
use Monolog\Logger;

class LogHelper
{
    public static function logEventByUser($eventName, $user = null, $eventParams = [])
    {
        /**
         * @var Logger $logger
         */
        $monologComponent = Yii::$app->monolog;
        $logger = $monologComponent->getLogger('main');

        if ($user === null) {
            $user = Yii::$app->user->identity;
        }

        if ($user === null) {
            return false;
        }

        $logger->info(
            $eventName,
            [
                'user_id'    => $user->id,
                'username'   => $user->username,
                'kabkota_id' => $user->kabkota_id ? (int) $user->kabkota_id : null,
                'kec_id'     => $user->kec_id ? (int) $user->kec_id : null,
                'kel_id'     => $user->kel_id ? (int) $user->kel_id : null,
                'role'       => (int) $user->role,
                'status'     => $user->status,
            ]
        );
    }
}
