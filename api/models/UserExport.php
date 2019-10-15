<?php

namespace app\models;

use Carbon\Carbon;
use yii\base\Model;
use Illuminate\Support\Arr;
use yii\data\SqlDataProvider;

class UserExport extends Model
{
    public function getUserExport($params)
    {
        $conditional = '';
        $limit = Arr::get($params, 'limit');
        $paramsSql = [':status_deleted' => User::STATUS_DELETED];


        // Exclude saber hoax
        $showSaberhoax = Arr::get($params, 'show_saberhoax');
        if ($showSaberhoax) {
            $conditional .= 'AND u.role <> :role_saberhoax ';
            $paramsSql[':role_saberhoax'] = User::ROLE_STAFF_SABERHOAX;
        }

        // Filtering
        $maxRoleRange = Arr::get($params, 'max_role_range');
        if ($maxRoleRange) {
            $conditional .= 'AND u.role BETWEEN 0 AND :max_role_range ';
            $paramsSql[':max_role_range'] = $maxRoleRange;
        }

        $kabkotaId = Arr::get($params, 'kabkota_id');
        if ($kabkotaId) {
            $conditional .= 'AND u.kabkota_id = :kabkota_id ';
            $paramsSql[':kabkota_id'] = $kabkotaId;
        }

        $kecId = Arr::get($params, 'kec_id');
        if ($kecId) {
            $conditional .= 'AND u.kec_id = :kec_id ';
            $paramsSql[':kec_id'] = $kecId;
        }

        $kelId = Arr::get($params, 'kel_id');
        if ($kelId) {
            $conditional .= 'AND u.kel_id = :kel_id ';
            $paramsSql[':kel_id'] = $kelId;
        }

        $rw = Arr::get($params, 'rw');
        if ($rw) {
            $conditional .= 'AND u.rw = :rw ';
            $paramsSql[':rw'] = $rw;
        }

        $username = Arr::get($params, 'username');
        if ($username) {
            $conditional .= 'AND u.username LIKE "%:username%" ';
            $paramsSql[':username'] = $username;
        }

        $name = Arr::get($params, 'name');
        if ($name) {
            $conditional .= 'AND u.name LIKE "%:name%" ';
            $paramsSql[':name'] = $name;
        }

        $phone = Arr::get($params, 'phone');
        if ($phone) {
            $conditional .= 'AND a.phone LIKE "%:phone%" ';
            $paramsSql[':phone'] = $phone;
        }

        $status = Arr::get($params, 'status');
        if (isset($status)) {
            $query->andWhere(['u.status' => $status]);

            $conditional .= 'AND a.category_id = :category_id ';
            $paramsSql[':category_id'] = $categoryId;
        }

        $profileCompleted = Arr::get($params, 'profile_completed');
        if (isset($profile_completed)) {
            $profileCompleted = ($profileCompleted === 'true') ? 'IS NOT' : 'IS';
            $conditional .= 'AND a.profile_updated_at $profileCompleted NULL';
        }

        $sql = "
                SELECT
                    u.id,
                    username,
                    email,
                    unconfirmed_email,
                    DATE_FORMAT(FROM_UNIXTIME(confirmed_at), '%d-%m-%Y') AS confirmed_at,
                    DATE_FORMAT(FROM_UNIXTIME(last_login_at), '%d-%m-%Y') AS last_login_at,
                    registration_ip,
                    last_login_ip,
                    u.status,
                    CASE role
                        WHEN 10 THEN 'user'
                        WHEN 49 THEN 'trainer'
                        WHEN 50 THEN 'staffRW'
                        WHEN 60 THEN 'staffKel'
                        WHEN 70 THEN 'staffKec'
                        WHEN 80 THEN 'staffKabkota'
                        WHEN 89 THEN 'staffSaberhoax'
                        WHEN 90 THEN 'staffProv'
                        WHEN 99 THEN 'admin'
                    end role,
                    DATE_FORMAT(FROM_UNIXTIME(u.created_at), '%d-%m-%Y') AS created_at,
                    DATE_FORMAT(FROM_UNIXTIME(u.updated_at), '%d-%m-%Y') AS updated_at,
                    u.name,
                    phone,
                    address,
                    rt,
                    rw,
                    kel.name AS kel_name,
                    kec.name AS kec_name,
                    kabkota.name AS kabkota_name,
                    facebook,
                    twitter,
                    instagram,
                    DATE_FORMAT(FROM_UNIXTIME(password_updated_at), '%d-%m-%Y') AS password_updated_at,
                    DATE_FORMAT(FROM_UNIXTIME(profile_updated_at), '%d-%m-%Y') AS profile_updated_at,
                    last_access_at
                FROM user u
                LEFT JOIN areas kabkota ON kabkota.id = u.kabkota_id
                LEFT JOIN areas kec ON kec.id = u.kec_id
                LEFT JOIN areas kel ON kel.id = u.kel_id
                WHERE u.status <> :status_deleted
                $conditional
        ";

        $provider = new SqlDataProvider([
            'sql'      => $sql,
            'params'   => $paramsSql,
            'pagination' => [
                'pageSize' => User::MAX_ROWS_EXPORT,
            ],
        ]);

        return $provider;
    }
}
