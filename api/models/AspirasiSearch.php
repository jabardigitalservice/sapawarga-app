<?php

namespace app\models;

use app\components\ModelHelper;
use Illuminate\Support\Arr;
use yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * AspirasiSearch represents the model behind the search form of `app\models\Aspirasi`.
 */
class AspirasiSearch extends Aspirasi
{
    /**
     * @var \app\models\User
     */
    public $user;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            //
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @param bool $onlyMe
     * @return ActiveDataProvider
     */
    public function search($params, $onlyMe = false)
    {
        $query = Aspirasi::find();

        $query->joinWith(['category']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere(['id' => $this->id]);

        $query->andFilterWhere(['<>', 'aspirasi.status', Aspirasi::STATUS_DELETED]);

        if (Arr::has($params, 'title')) {
            $query->andWhere(['like', 'title', Arr::get($params, 'title')]);
        }

        if (Arr::has($params, 'description')) {
            $query->andWhere(['like', 'description', Arr::get($params, 'description')]);
        }

        // Untuk list GET /aspirasi/me
        if ($onlyMe) {
            return $this->getQueryMe($query, $params);
        }

        return $this->getQueryAll($query, $params);
    }

    public function searchMobile(&$params, $onlyMe = false)
    {
    }

    public function searchWebadmin(&$params, $onlyMe = false)
    {
    }

    protected function getQueryMe($query, $params)
    {
        $query->andFilterWhere(['author_id' => $this->author_id]);

        $statuses = [
            Aspirasi::STATUS_DRAFT,
            Aspirasi::STATUS_APPROVAL_PENDING,
            Aspirasi::STATUS_APPROVAL_REJECTED,
            Aspirasi::STATUS_PUBLISHED,
        ];

        $query->andFilterWhere(['in', 'aspirasi.status', $statuses]);

        $pageLimit = Arr::get($params, 'limit');
        $sortBy    = Arr::get($params, 'sort_by', 'created_at');
        $sortOrder = Arr::get($params, 'sort_order', 'descending');
        $sortOrder = $this->getSortOrder($sortOrder);

        return new ActiveDataProvider([
            'query' => $query,
            'sort'=> ['defaultOrder' => [$sortBy => $sortOrder]],
            'pagination' => [
                'pageSize' => $pageLimit,
            ],
        ]);
    }

    protected function getQueryAll($query, $params)
    {
        $this->filterByStatus($query, $params);
        $this->filterByArea($query, $params);
        $this->filterByCategory($query, $params);

        $pageLimit = Arr::get($params, 'limit');
        $sortBy    = Arr::get($params, 'sort_by', 'created_at');
        $sortOrder = Arr::get($params, 'sort_order', 'descending');
        $sortOrder = $this->getSortOrder($sortOrder);

        $provider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> [
                'defaultOrder' => [$sortBy => $sortOrder],
            ],
            'pagination' => [
                'pageSize' => $pageLimit,
            ],
        ]);

        $provider->sort->attributes['category.name'] = [
            'asc'  => ['categories.name' => SORT_ASC],
            'desc' => ['categories.name' => SORT_DESC],
        ];

        return $provider;
    }

    protected function filterByStatus(&$query, $params)
    {
        $filterStatusList = [];

        // Jika User, hanya bisa melihat yang status published
        if (Yii::$app->user->can('aspirasiMobile')) {
            $filterStatusList = [
                Aspirasi::STATUS_PUBLISHED,
            ];
        }

        // Filter status untuk role Admin hingga staffKel
        if (Yii::$app->user->can('aspirasiWebadminManage')
            || Yii::$app->user->can('aspirasiWebadminView')) {
            if (Arr::has($params, 'status')) {
                $filterStatusList = [ $params['status'] ];
            } else {
                $filterStatusList = [
                    Aspirasi::STATUS_APPROVAL_PENDING,
                    Aspirasi::STATUS_APPROVAL_REJECTED,
                    Aspirasi::STATUS_UNPUBLISHED,
                    Aspirasi::STATUS_PUBLISHED,
                ];
            }
        }

        if (count($filterStatusList) > 0) {
            $query->andFilterWhere(['in', 'aspirasi.status', $filterStatusList]);
        }
    }

    protected function filterByArea(&$query, $params)
    {
        if (Arr::has($params, 'kabkota_id') || Arr::has($params, 'kec_id') || Arr::has($params, 'kel_id')) {
            ModelHelper::filterByAreaTopDown($query, $params);
        } else {
            // Jika Staf Kab/Kota, Staf Kec, dan Staf Kel, default filter berdasarkan area Staf tersebut
            if (Yii::$app->user->can('aspirasiWebadminView') === true) {
                $areaParams = [
                'kabkota_id' => $this->user->kabkota_id ?? null,
                'kec_id' => $this->user->kec_id ?? null,
                'kel_id' => $this->user->kel_id ?? null,
                ];
                ModelHelper::filterByAreaTopDown($query, $areaParams);
            }
        }
    }

    protected function filterByCategory(&$query, $params)
    {
        if (Arr::has($params, 'category_id')) {
            $query->andFilterWhere(['category_id' => $params['category_id']]);
        }
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
