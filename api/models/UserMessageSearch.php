<?php

namespace app\models;

use app\components\ModelHelper;
use Illuminate\Support\Arr;
use yii\data\ActiveDataProvider;

/**
 * UserMessageSearch represents the model behind the search form of `app\models\UserMessage`.
 */
class UserMessageSearch extends UserMessage
{
    public $userRole;

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = UserMessage::find();

        // Mandatory conditions
        $query->andFilterWhere(['<>', 'status', 0]);
        $query->andFilterWhere(['=', 'recipient_id', $params['user_id']]);

        // Filtering conditions
        $searchKeyword = Arr::get($params, 'search');

        $query->andFilterWhere(['like', 'title', $searchKeyword]);
        $query->orFilterWhere(['like', 'content', $searchKeyword]);
        $query->andFilterWhere(['id' => $this->id]);

        return $this->getQueryAll($query, $params);
    }

    protected function getQueryAll($query, $params)
    {
        $pageLimit = Arr::get($params, 'limit');
        $sortBy    = Arr::get($params, 'sort_by', 'created_at');
        $sortOrder = Arr::get($params, 'sort_order', 'descending');
        $sortOrder = ModelHelper::getSortOrder($sortOrder);

        return new ActiveDataProvider([
            'query'      => $query,
            'sort'       => [
                'defaultOrder' => [$sortBy => $sortOrder],
                'attributes' => [
                    'title',
                    'status',
                    'created_at'
                ],
            ],
            'pagination' => [
                'pageSize' => $pageLimit,
            ],
        ]);
    }
}
