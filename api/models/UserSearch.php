<?php

namespace app\models;

use Carbon\Carbon;
use yii\base\Model;
use yii\behaviors\AttributeTypecastBehavior;
use yii\data\ActiveDataProvider;
use yii\db\ActiveQuery;

class UserSearch extends Model
{
    public $search;
    public $range_roles = [];
    public $not_in_status = [];
    public $show_saberhoax;
    public $show_trainer;

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
            ['profile_completed', 'boolean', 'trueValue' => 'true', 'falseValue' => 'false', 'strict' => true],
        ];
    }

    public function formName()
    {
        return '';
    }

    public function getDataProvider()
    {
        // Create default query;
        $query = $this->newQuery();

        // Filter by role
        if ($this->role_id) {
            $query->andWhere(['role' => User::ROLE_MAP[$this->role_id]]);
        }

        // Filter by area id
        $query = $this->buildQueryArea($query);

        // Filter by last access (between start/end)
        $query = $this->buildQueryLastAccess($query);

        // Filter by fields
        $query = $this->buildQueryFields($query);

        // Filter by Profile Completion
        $query = $this->buildQueryProfileCompleted($query);

        // Filter by Status
        if (isset($this->status)) {
            $query->andWhere(['user.status' => $this->status]);
        }

        return $this->buildActiveProvider($query);
    }

    protected function newQuery()
    {
        // TODO refactor this
        $query = User::find()
            ->where(['not in', 'user.status', $this->not_in_status])
            ->andWhere(['between', 'user.role', $this->range_roles[0], $this->range_roles[1]]);

        // Exclude Trainer
        if (!$this->show_trainer) {
            $query->andWhere(['<>', 'user.role', User::ROLE_TRAINER]);
        }

        // Exclude saber hoax
        if (!$this->show_saberhoax) {
            $query->andWhere(['<>', 'user.role', User::ROLE_STAFF_SABERHOAX]);
        }

        return $query;
    }

    protected function buildQueryArea(ActiveQuery $query)
    {
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

        return $query;
    }

    protected function buildQueryLastAccess(ActiveQuery $query)
    {
        if ($this->last_access_start && $this->last_access_end) {
            $lastAccessStart = (new Carbon($this->last_access_start))->startOfDay();
            $lastAccessEnd   = (new Carbon($this->last_access_end))->endOfDay();

            $query->andWhere(['>=', 'last_access_at', $lastAccessStart]);
            $query->andWhere(['<=', 'last_access_at', $lastAccessEnd]);
        }

        return $query;
    }

    protected function buildQueryFields(ActiveQuery $query)
    {
        if ($this->username) {
            $query->andWhere(['like', 'user.username', $this->username]);
        }

        if ($this->name) {
            $query->andWhere(['like', 'user.name', $this->name]);
        }

        if ($this->phone) {
            $query->andWhere(['like', 'user.phone', $this->phone]);
        }

        return $query;
    }

    protected function buildQueryProfileCompleted(ActiveQuery $query)
    {
        if (isset($this->profile_completed)) {
            $conditional = ($this->profile_completed) ? 'is not' : 'is';
            $query->andWhere([$conditional, 'user.profile_updated_at', null]);
        }

        return $query;
    }

    protected function buildActiveProvider(ActiveQuery $query)
    {
        $this->sort_by = $this->sort_by ?? 'name';
        $this->sort_order = $this->getSortOrder($this->sort_order);

        return new ActiveDataProvider([
            'query' => $query,
            'sort'=> ['defaultOrder' => [$this->sort_by => $this->sort_order]],
            'pagination' => [
                'pageSize' => $this->limit,
            ],
        ]);
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

    public function behaviors()
    {
        return [
            'typecast' => [
                'class' => AttributeTypecastBehavior::class,
                'attributeTypes' => [
                    // from URL query, get string value :( (should be boolean)
                    'profile_completed' => function ($value) {
                        return $value === 'true';
                    }
                ],
                'typecastAfterValidate' => true,
            ],
        ];
    }
}
