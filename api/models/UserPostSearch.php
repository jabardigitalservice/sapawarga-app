<?php

namespace app\models;

use app\components\ModelHelper;
use Illuminate\Support\Arr;
use yii\data\ActiveDataProvider;
use Yii;

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
        $query = UserPost::find()->with('comments')
                ->select([
                    '{{user_posts}}.*',
                    'COUNT({{likes}}.id) AS likes_count'
                ])
                ->joinWith('likes')
                ->groupBy('{{user_posts}}.id');

        // Filtering
        $query->andFilterWhere(['like', 'text',  Arr::get($params, 'search')]);
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
        $query->andWhere(['user_posts.status' => UserPost::STATUS_ACTIVE]);
        $query->orWhere(['and',
            ['user_posts.status' => UserPost::STATUS_DISABLED],
            ['user_posts.created_by' => Yii::$app->user->id]
        ]);

        return $this->getQueryAll($query, $params);
    }

    protected function getQueryAll($query, $params)
    {
        $pageLimit = Arr::get($params, 'limit');
        $sortBy    = Arr::get($params, 'sort_by', 'likes_count');
        $sortOrder = Arr::get($params, 'sort_order', 'descending');
        $sortOrder = ModelHelper::getSortOrder($sortOrder);

        return new ActiveDataProvider([
            'query'      => $query,
            'sort'       => [
                'defaultOrder' => [$sortBy => $sortOrder],
                'attributes' => [
                    'text',
                    'created_at',
                    'status',
                    'likes_count',
                ],
            ],
            'pagination' => [
                'pageSize' => $pageLimit,
            ],
        ]);
    }
}
