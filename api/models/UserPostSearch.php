<?php

namespace app\models;

use app\components\ModelHelper;
use Illuminate\Support\Arr;
use yii\caching\DbDependency;
use yii\data\ActiveDataProvider;

/**
 * UserPostSearch represents the model behind the search form of `app\models\UserPost`.
 */
class UserPostSearch extends UserPost
{
    const SCENARIO_LIST_USER = 'list-user';

    public $cacheDependency;

    public function __construct($config = [])
    {
        if (getenv('YII_ENV_DEV') != 1) {
            $this->cacheDependency = new DbDependency();
            $this->cacheDependency->sql = 'SELECT count(*) FROM ' . UserPost::tableName();
        }
        parent::__construct();
    }

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

        if (getenv('YII_ENV_DEV') != 1) {
            $query->cache(true, $this->cacheDependency);
        }

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
