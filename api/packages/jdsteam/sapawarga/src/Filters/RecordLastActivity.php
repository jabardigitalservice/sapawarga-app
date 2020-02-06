<?php

namespace Jdsteam\Sapawarga\Filters;

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

        $this->logInfo();

        return parent::afterAction($action, $result);
    }

    protected function logInfo()
    {
        $user = Yii::$app->user->identity;

        /**
         * @var Logger $logger
         */
        $monologComponent = Yii::$app->monolog;
        $logger = $monologComponent->getLogger('main');

        $logger->info(
            'USER_LAST_ACTIVITY',
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
