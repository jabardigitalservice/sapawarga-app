<?php


namespace app\modules\v1\repositories;

use Yii;
use app\models\User;

class UserRepository
{
    public function getDescendantRoles($roleName)
    {
        // TODO Role should not hard coded here
        $descendantRoles = [];

        if ($roleName === 'staffKel') {
            $descendantRoles = ['staffRW', 'user'];
        }

        if ($roleName === 'staffKec') {
            $descendantRoles = ['staffRW', 'staffKel', 'user'];
        }

        if ($roleName === 'staffKabkota') {
            $descendantRoles = ['staffRW', 'staffKel', 'staffKec', 'user'];
        }

        if ($roleName === 'staffProv') {
            $descendantRoles = [
                'staffRW', 'staffKel', 'staffKec', 'staffKabkota', 'trainer', 'user',
            ];
        }

        if ($roleName === 'admin') {
            $descendantRoles = [
                'staffRW', 'staffKel', 'staffKec', 'staffKabkota','staffProv', 'staffSaberhoax', 'trainer', 'user',
            ];
        }

        return $descendantRoles;
    }
    
    public function getUsersCountAllRolesByArea($selectedRoles, $kabKotaId, $kecId, $kelId): array
    {
        $roles = Yii::$app->authManager->getRoles();

        $items = [];
        $index = 1;

        foreach ($roles as $role) {
            if (in_array($role->name, $selectedRoles) === false) {
                continue;
            }

            $items[] = [
                'id'    => $index,
                'level' => $role->name,
                'name'  => $role->description,
                'value' => $this->getUsersCountRoleByArea($role->name, $kabKotaId, $kecId, $kelId),
            ];

            $index++;
        }

        return $items;
    }

    public function getUsersCountRoleByArea($roleName, $kabKotaId, $kecId, $kelId): int
    {
        $query = User::find()
            ->select('user.*')
            ->innerJoin('auth_assignment', '`auth_assignment`.`user_id` = `user`.`id`')
            ->andWhere(['auth_assignment.item_name' => $roleName])
            ->andWhere(['user.status' => User::STATUS_ACTIVE])
            ->andWhere(['!=', 'user.status', User::STATUS_DELETED]);

        if ($kabKotaId) {
            $query->andWhere(['user.kabkota_id' => $kabKotaId]);
        }

        if ($kecId) {
            $query->andWhere(['user.kec_id' => $kecId]);
        }

        if ($kelId) {
            $query->andWhere(['user.kel_id' => $kelId]);
        }

        return $query->count();
    }
}
