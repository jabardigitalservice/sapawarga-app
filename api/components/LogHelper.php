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

        return $logger->info(
            $eventName,
            [
                'user_id'     => $user->id,
                'username'    => $user->username,
                'kabkota_id'  => $user->kabkota_id ? (int)$user->kabkota_id : null,
                'kabkota_bps' => $user->kel_id ? $user->kabkota->code_bps : null,
                'kabkota'     => $user->kel_id ? $user->kabkota->name : null,
                'kec_id'      => $user->kec_id ? (int)$user->kec_id : null,
                'kec_bps'     => $user->kel_id ? $user->kecamatan->code_bps : null,
                'kecamatan'   => $user->kel_id ? $user->kecamatan->name : null,
                'kel_id'      => $user->kel_id ? (int)$user->kel_id : null,
                'kel_bps'     => $user->kel_id ? $user->kelurahan->code_bps : null,
                'kelurahan'   => $user->kel_id ? $user->kelurahan->name : null,
                'role'        => (int)$user->role,
                'status'      => $user->status,
            ]
        );
    }
}
