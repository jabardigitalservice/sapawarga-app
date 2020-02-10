<?php

namespace app\models;

use app\components\ModelHelper;
use Illuminate\Support\Arr;
use Yii;
use yii\data\ActiveDataProvider;

/**
 * NewsImportantSearch represents the model behind the search form of `app\models\NewsImportant`.
 */
class NewsImportantSearch extends NewsImportant
{
    const SCENARIO_LIST_STAFF = 'list-staff';
    const SCENARIO_LIST_USER = 'list-user';

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = NewsImportant::find();
        $query->andFilterWhere(['like', 'title', Arr::get($params, 'search')]);
        $query->andFilterWhere(['=', 'category_id', Arr::get($params, 'category_id')]);

        $allLocation = Arr::get($params, 'all_location');

        if ($allLocation == true) {
            $query->andWhere(['is', 'news_important.kabkota_id', null]);
        } else {
            $this->filterByKabkota($query, $params);
        }

        if ($this->scenario === self::SCENARIO_LIST_STAFF) {
            return $this->getQueryListStaff($query, $params);
        }

        return $this->getQueryListUser($query, $params);
    }

    protected function getQueryListStaff($query, $params)
    {
        $query->joinWith(['category']);
        $query->andFilterWhere(['<>', 'news_important.status', NewsImportant::STATUS_DELETED]);
        $query->andFilterWhere(['id' => $this->id]);

        if (Arr::has($params, 'status')) {
            $query->andFilterWhere(['news_important.status' => Arr::get($params, 'status')]);
        }

        return $this->getQueryAll($query, $params);
    }

    protected function getQueryListUser($query, $params)
    {
        $query->andFilterWhere(['=', 'news_important.status', NewsImportant::STATUS_ACTIVE]);

        return $this->getQueryAll($query, $params);
    }

    protected function getQueryAll($query, $params)
    {
        $pageLimit = Arr::get($params, 'limit');
        $sortBy    = Arr::get($params, 'sort_by', 'created_at');
        $sortOrder = Arr::get($params, 'sort_order', 'descending');
        $sortOrder = ModelHelper::getSortOrder($sortOrder);

        return new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [$sortBy => $sortOrder],
                'attributes' => [
                    'title',
                    'category_id',
                    'status',
                    'likes_count',
                    'created_at',
                    'category.name' => [
                        'asc'  => ['categories.name' => SORT_ASC],
                        'desc' => ['categories.name' => SORT_DESC],
                    ],
                ],
            ],
            'pagination' => [
                'pageSize' => $pageLimit,
            ],
        ]);
    }

    protected function filterByKabkota($query, $params)
    {
        if ($this->scenario === self::SCENARIO_LIST_USER) {
            // Auto-filter by user's kabkota_id
            $authUser = User::findIdentity(Yii::$app->user->getId());
            $query->andWhere(['or',
                ['kabkota_id' => $authUser->kabkota_id],
                ['kabkota_id' => null]]);
        } elseif ($this->scenario === self::SCENARIO_LIST_STAFF) {
            $kabkotaId = Arr::get($params, 'kabkota_id');
            if ($kabkotaId) {
                $query->andFilterWhere(['kabkota_id' => $kabkotaId]);
            }
        }
    }
}
