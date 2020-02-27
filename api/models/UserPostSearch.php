<?php

namespace app\models;

use app\components\ModelHelper;
use Illuminate\Support\Arr;
use yii\data\ActiveDataProvider;

/**
 * UserPostSearch represents the model behind the search form of `app\models\UserPost`.
 */
class UserPostSearch extends UserPost
{
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
        $query = UserPost::find();
        $query->joinWith(['author']);

        // Filtering
        $query->andFilterWhere(['=', 'user.kabkota_id',  Arr::get($params, 'kabkota_id')]);
        $query->andFilterWhere(['like', 'user.name',  Arr::get($params, 'name')]);
        $query->andFilterWhere(['like', 'text',  Arr::get($params, 'text')]);
        $query->andFilterWhere(['user_posts.status' => Arr::get($params, 'status')]);

        // Filtering by status
        if ($this->scenario === self::SCENARIO_LIST_USER) {
            return $this->getQueryListUser($query, $params);
        } else {
            $query->andWhere(['<>', 'user_posts.status', UserPost::STATUS_DELETED]);
        }

        return $this->getQueryAll($query, $params);
    }

    protected function getQueryListUser($query, $params)
    {
        $query->andFilterWhere([
            'or',
            ['like', 'text', Arr::get($params, 'search')],
            ['like', 'user.name', Arr::get($params, 'search')],
        ]);

        // Query for my post or public post
        if (! empty($this->created_by)) {
            $query->andWhere(['created_by' => $this->created_by]);
            $query->andWhere(['<>', 'user_posts.status', UserPost::STATUS_DELETED]);
        } else {
            $query->andWhere(['user_posts.status' => UserPost::STATUS_ACTIVE]);
        }

        return $this->getQueryAll($query, $params);
    }

    protected function getQueryAll($query, $params)
    {
        $pageLimit = Arr::get($params, 'limit');
        $sortBy = Arr::get($params, 'sort_by', 'created_at');
        $sortOrder = Arr::get($params, 'sort_order', 'descending');
        $sortOrder = ModelHelper::getSortOrder($sortOrder);

        return new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [$sortBy => $sortOrder],
                'attributes' => [
                    'text',
                    'created_at',
                    'status',
                    'likes_count',
                    'comments_count',
                ],
            ],
            'pagination' => [
                'pageSize' => $pageLimit,
            ],
        ]);
    }
}
