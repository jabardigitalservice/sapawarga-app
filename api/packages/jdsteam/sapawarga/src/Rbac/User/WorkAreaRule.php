<?php

namespace Jdsteam\Sapawarga\Rbac\User;

use app\models\User;
use yii\rbac\Rule;

class WorkAreaRule extends Rule
{
    public $name = 'isWorkArea';

    public function execute($authUserId, $permission, $params)
    {
        /**
         * @var User $record
         */
        $record   = $params['record'];
        $authUser = User::findOne(['id' => $authUserId]);

        if ($this->hasScopeKabkota($authUser)) {
            return $record->kabkota_id === $authUser->kabkota_id;
        }

        if ($this->hasScopeKecamatan($authUser)) {
            return $record->kec_id === $authUser->kec_id;
        }

        if ($this->hasScopeKelurahan($authUser)) {
            return $record->kel_id === $authUser->kel_id;
        }
    }

    protected function hasScopeKabkota(User $authUser): bool
    {
        // TODO find other ways (avoid hardcoded role name)
        return $authUser->kabkota_id !== null && $authUser->kec_id === null && $authUser->kel_id === null;
    }

    protected function hasScopeKecamatan(User $authUser): bool
    {
        // TODO find other ways (avoid hardcoded role name)
        return $authUser->kabkota_id !== null && $authUser->kec_id !== null && $authUser->kel_id === null;
    }

    protected function hasScopeKelurahan(User $authUser): bool
    {
        // TODO find other ways (avoid hardcoded role name)
        return $authUser->kabkota_id !== null && $authUser->kec_id !== null && $authUser->kel_id !== null;
    }
}
