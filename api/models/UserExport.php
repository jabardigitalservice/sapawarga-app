<?php

namespace app\models;

use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use Yii;
use yii\base\Model;
use yii\db\Query;

class UserExport extends Model
{
    public function getUserExport($params)
    {
        // TODO refactor to UserSearch
        $query = new Query;
        $query->select([
                    'user.id',
                    'username',
                    'email',
                    'user.role',
                    'unconfirmed_email',
                    'registration_ip',
                    'last_login_ip',
                    'user.status',
                    'user.name',
                    'phone',
                    'address',
                    'rt',
                    'rw',
                    'kel.name AS kel_name',
                    'kec.name AS kec_name',
                    'kabkota.name AS kabkota_name',
                    'facebook',
                    'twitter',
                    'instagram',
                    'DATE_FORMAT(FROM_UNIXTIME(confirmed_at),"%d-%m-%Y") AS confirmed_at',
                    'DATE_FORMAT(FROM_UNIXTIME(last_login_at),"%d-%m-%Y") AS last_login_at',
                    'DATE_FORMAT(FROM_UNIXTIME(user.created_at), "%d-%m-%Y") AS created_at',
                    'DATE_FORMAT(FROM_UNIXTIME(user.updated_at), "%d-%m-%Y") AS updated_at',
                    'DATE_FORMAT(FROM_UNIXTIME(password_updated_at), "%d-%m-%Y") AS password_updated_at',
                    'DATE_FORMAT(FROM_UNIXTIME(profile_updated_at), "%d-%m-%Y") AS profile_updated_at',
                    'DATE_FORMAT(FROM_UNIXTIME(last_access_at), "%d-%m-%Y") AS last_access_at',
            ])
            ->from('user')
            ->leftJoin('areas kabkota', '`kabkota`.`id` = `user`.`kabkota_id`')
            ->leftJoin('areas kec', '`kec`.`id` = `user`.`kec_id`')
            ->leftJoin('areas kel', '`kel`.`id` = `user`.`kel_id`')
            ->where(['<>', 'user.status', User::STATUS_DELETED]);

        // Filtering by role
        if (Arr::get($params, 'show_saberhoax') === false) {
            $query->andWhere(['<>', 'user.role', User::ROLE_STAFF_SABERHOAX]);
        }

        if (Arr::get($params, 'show_trainer') === false) {
            $query->andWhere(['<>', 'user.role', User::ROLE_TRAINER]);
        }

        if (Arr::get($params, 'role_id')) {
            $query->andWhere(['role' => User::ROLE_MAP[Arr::get($params, 'role_id')]]);
        }
        if (Arr::get($params, 'max_roles')) {
            $query->andWhere(['between', 'user.role', 0, Arr::get($params, 'max_roles')]);
        }

        // Filtering location
        if (Arr::get($params, 'kabkota_id')) {
            $query->andWhere(['kabkota_id' => Arr::get($params, 'kabkota_id')]);
        }
        if (Arr::get($params, 'kec_id')) {
            $query->andWhere(['kec_id' => Arr::get($params, 'kec_id')]);
        }
        if (Arr::get($params, 'kel_id')) {
            $query->andWhere(['kel_id' => Arr::get($params, 'kel_id')]);
        }
        if (Arr::get($params, 'rw')) {
            $query->andWhere(['rw' => Arr::get($params, 'rw')]);
        }

        if (Arr::get($params, 'last_access_start') && Arr::get($params, 'last_access_end')) {
            $lastAccessStart = (new Carbon(Arr::get($params, 'last_access_start')))->startOfDay();
            $lastAccessEnd   = (new Carbon(Arr::get($params, 'last_access_end')))->endOfDay();

            $query->andWhere(['>=', 'last_access_at', $lastAccessStart]);
            $query->andWhere(['<=', 'last_access_at', $lastAccessEnd]);
        }

        if (Arr::get($params, 'search')) {
            $query->andWhere([
                'or',
                ['like', 'user.name', Arr::get($params, 'search')],
                ['like', 'user.phone', Arr::get($params, 'search')],
            ]);
        }

        if (Arr::get($params, 'username')) {
            $query->andWhere(['like', 'user.username', Arr::get($params, 'username')]);
        }

        if (Arr::get($params, 'name')) {
            $query->andWhere(['like', 'user.name', Arr::get($params, 'name')]);
        }

        if (Arr::get($params, 'phone')) {
            $query->andWhere(['like', 'user.phone', Arr::get($params, 'phone')]);
        }

        if (Arr::get($params, 'status')) {
            $query->andWhere(['user.status' => Arr::get($params, 'status')]);
        }

        if (Arr::get($params, 'profile_completed')) {
            $conditional = (Arr::get($params, 'profile_completed') == 'true') ? 'is not' : 'is';
            $query->andWhere([$conditional, 'user.profile_updated_at', null]);
        }

        $query->limit(User::MAX_ROWS_EXPORT_ALLOWED);

        return $query;
    }

    public function generateFile($params)
    {
        $nowDate  = date('Ymd-His');
        $filename = "export-user-$nowDate.xlsx";

        $filePath = Yii::getAlias('@webroot/storage') . '/' . $filename;

        // Write to local file
        $writer = WriterEntityFactory::createXLSXWriter();
        $writer->openToFile($filePath);

        $columns = $this->getHeaderColumns();
        $writer->addRow(WriterEntityFactory::createRowFromArray($columns));

        $search = $this->getUserExport($params);

        foreach ($search->each() as $key => $user) {
            $this->insertRow($writer, $user);
        }

        $writer->close();

        return $filePath;
    }

    protected function insertRow(&$writer, $user)
    {
        $row = WriterEntityFactory::createRowFromArray([
            $user['id'],
            $user['role'],
            $user['username'],
            $user['name'],
            $user['email'],
            $user['confirmed_at'],
            $user['status'],
            $user['created_at'],
            $user['updated_at'],
            $user['phone'],
            $user['address'],
            $user['kabkota_name'],
            $user['kec_name'],
            $user['kel_name'],
            $user['rw'],
            $user['rt'],
            $user['password_updated_at'],
            $user['profile_updated_at'],
            $user['last_access_at'],
        ]);

        $writer->addRow($row);

        return $writer;
    }

    protected function getHeaderColumns()
    {
        return [
            'id', 'role', 'username', 'name', 'email', 'confirmed_at', 'status', 'created_at', 'updated_at',
            'phone', 'address', 'kabkota', 'kecamatan', 'kelurahan', 'rw', 'rt', 'password_updated_at',
            'profile_updated_at', 'last_access_at',
        ];
    }
}
