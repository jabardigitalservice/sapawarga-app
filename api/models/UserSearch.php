<?php

namespace app\models;

use Carbon\Carbon;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class UserSearch extends Model
{
    public $search;
    public $range_roles = [];
    public $not_in_status = [];
    public $show_saberhoax;

    public $name;
    public $username;
    public $phone;

    public $role_id;
    public $kabkota_id;
    public $kec_id;
    public $kel_id;
    public $rw;

    public $last_access_start;
    public $last_access_end;

    public $status;

    public $profile_completed;
    public $limit;
    public $sort_by;
    public $sort_order;

    public function rules()
    {
        return [
            [['search'], 'string', 'max' => 50],
            [['limit', 'status'], 'integer'],
            [['last_access_start', 'last_access_end'], 'default'],
            [
                [
                    'name', 'username', 'phone',
                    'role_id', 'kabkota_id', 'kec_id', 'kel_id', 'rw',
                    'sort_by', 'sort_order',
                    'profile_completed'
                ],
                'string'
            ],
            ['profile_completed', 'in', 'range' => ['true', 'false']],
        ];
    }

    public function formName()
    {
        return '';
    }

    public function getDataProvider()
    {
        $query = User::find()
            ->where(['not in', 'user.status', $this->not_in_status])
            ->andWhere(['between', 'user.role', $this->range_roles[0], $this->range_roles[1]]);

        // Exclude saber hoax
        if (!$this->show_saberhoax) {
            $query->andWhere(['<>', 'user.role', User::ROLE_STAFF_SABERHOAX]);
        }

        // Filter by role
        if ($this->role_id) {
            $query->andWhere(['role' => User::ROLE_MAP[$this->role_id]]);
        }

        // Filter by area id
        if ($this->kabkota_id) {
            $query->andWhere(['kabkota_id' => $this->kabkota_id]);
        }
        if ($this->kec_id) {
            $query->andWhere(['kec_id' => $this->kec_id]);
        }
        if ($this->kel_id) {
            $query->andWhere(['kel_id' => $this->kel_id]);
        }
        if ($this->rw) {
            $query->andWhere(['rw' => $this->rw]);
        }

        if ($this->last_access_start && $this->last_access_end) {
            $lastAccessStart = (new Carbon($this->last_access_start))->startOfDay();
            $lastAccessEnd   = (new Carbon($this->last_access_end))->endOfDay();

            $query->andWhere(['>=', 'last_access_at', $lastAccessStart]);
            $query->andWhere(['<=', 'last_access_at', $lastAccessEnd]);
        }

        if ($this->search) {
            $query->andWhere([
                'or',
                ['like', 'user.name', $this->search],
                ['like', 'user.phone', $this->search],
            ]);
        }

        if ($this->username) {
            $query->andWhere(['like', 'user.username', $this->username]);
        }

        if ($this->name) {
            $query->andWhere(['like', 'user.name', $this->name]);
        }

        if ($this->phone) {
            $query->andWhere(['like', 'user.phone', $this->phone]);
        }

        if (isset($this->status)) {
            $query->andWhere(['user.status' => $this->status]);
        }

        if (isset($this->profile_completed)) {
            $conditional = ($this->profile_completed == 'true') ? 'is not' : 'is';
            $query->andWhere([$conditional, 'user.profile_updated_at', null]);
        }

        $this->sort_by = $this->sort_by ?? 'name';
        $this->sort_order = $this->getSortOrder($this->sort_order);

        $provider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> ['defaultOrder' => [$this->sort_by => $this->sort_order]],
            'pagination' => [
                'pageSize' => $this->limit,
            ],
        ]);

        return $provider;
    }

    protected function getSortOrder($sortOrder)
    {
        switch ($sortOrder) {
            case 'descending':
                return SORT_DESC;
                break;
            case 'ascending':
            default:
                return SORT_ASC;
                break;
        }
    }
}
