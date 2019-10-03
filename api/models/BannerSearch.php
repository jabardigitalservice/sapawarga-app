<?php

namespace app\models;

use app\components\ModelHelper;
use Illuminate\Support\Arr;
use yii\data\ActiveDataProvider;

/**
 * BannerSearch represents the model behind the search form of `app\models\Banner`.
 */
class BannerSearch extends Banner
{
    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Banner::find();

        // filtering conditions
        $query->andFilterWhere(['id' => $this->id]);
        $query->andFilterWhere(['like', 'title', Arr::get($params, 'title')]);
        $query->andFilterWhere(['=', 'type', Arr::get($params, 'type')]);
        $query->andFilterWhere(['<>', 'status', Banner::STATUS_DELETED]);

        if (Arr::has($params, 'status')) {
            $query->andFilterWhere(['status' => Arr::get($params, 'status')]);
        }

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
                    'type',
                    'status',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => $pageLimit,
            ],
        ]);
    }
}
