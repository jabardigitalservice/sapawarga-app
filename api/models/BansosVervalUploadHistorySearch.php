<?php

namespace app\models;

use app\components\ModelHelper;
use Illuminate\Support\Arr;
use yii\data\ActiveDataProvider;

/**
 * BannerSearch represents the model behind the search form of `app\models\BansosVervalUploadHistory`.
 */
class BansosVervalUploadHistorySearch extends BansosVervalUploadHistory
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
        $query = BansosVervalUploadHistory::find();

        $query->andFilterWhere(['id' => $this->id]);
        $query->andFilterWhere(['like', 'original_filename', Arr::get($params, 'original_filename') . '%', false]);
        $query->andFilterWhere(['=', 'created_by', Arr::get($params, 'user_id')]);

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
                    'original_filename',
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
