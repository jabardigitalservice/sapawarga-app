<?php

namespace app\models;

use Illuminate\Support\Arr;
use yii\data\ActiveDataProvider;
use app\components\ModelHelper;

/**
 * GamificationActivitySearch represents the model behind the search form of `app\models\GamificationActivity`.
 */
class GamificationActivitySearch extends GamificationActivity
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
        $query = GamificationActivity::find()
                    ->joinWith('gamification', '`gamification`.`id` = `gamification_participants`.`gamification_id`');

        // Filtering
        $query->andFilterWhere(['gamification_id' => Arr::get($params, 'id')]);
        $query->andFilterWhere(['user_id' => Arr::get($params, 'user_id')]);

        return $this->createActiveDataProvider($query, $params);
    }

    protected function createActiveDataProvider($query, $params)
    {
        $pageLimit = false;
        $sortBy    = Arr::get($params, 'sort_by', 'created_at');
        $sortOrder = Arr::get($params, 'sort_order', 'ascending');
        $sortOrder = ModelHelper::getSortOrder($sortOrder);

        $provider = new ActiveDataProvider([
            'query'      => $query,
            'sort'       => [
                'defaultOrder' => [$sortBy => $sortOrder],
                'attributes' => [
                    'start_date',
                    'created_at',
                    'status',
                ],
            ],
            'pagination' => [
                'pageSize' => $pageLimit,
            ],
        ]);

        return $provider;
    }
}
