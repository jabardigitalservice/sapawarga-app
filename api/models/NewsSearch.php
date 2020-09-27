<?php

namespace app\models;

use app\components\ModelHelper;
use Illuminate\Support\Arr;
use yii\data\ActiveDataProvider;

/**
 * NewsSearch represents the model behind the search form of `app\models\News`.
 */
class NewsSearch extends News
{
    public const SCENARIO_LIST_USER = 'list-user';

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
        $query = News::find()->joinWith('channel')
                ->where(['<>', '{{news}}.status', News::STATUS_DELETED]);

        // grid filtering conditions
        $query->andFilterWhere(['id' => $this->id]);

        $filterChannelId = Arr::get($params, 'channel_id');
        $searchKeyword = Arr::get($params, 'search');
        $allLocation = Arr::get($params, 'all_location');

        $this->filterByKabkota($query, $params);

        $query->andFilterWhere(['channel_id' => $filterChannelId]);
        $query->andFilterWhere(['like', 'title', $searchKeyword]);
        $query->andFilterWhere(['<>', 'news.status', News::STATUS_DELETED]);
        $query->andFilterWhere(['news.status' => Arr::get($params, 'status')]);

        if ($allLocation == true) {
            $query->andWhere(['is', 'news.kabkota_id', null]);
        }

        if ($this->scenario === self::SCENARIO_LIST_USER) {
            return $this->getQueryListUser($query, $params);
        }

        return $this->getQueryAll($query, $params);
    }

    public function relatedList($params)
    {
        $query = News::find()->joinWith('channel');
        $query->andFilterWhere(['not in', 'news.id', Arr::get($params, 'id')]);
        $query->andFilterWhere(['news.status' => News::STATUS_ACTIVE]);

        $this->filterByKabkota($query, $params);

        $params['sort_by']    = 'seq';
        $params['sort_order'] = 'ascending';

        return $this->getQueryAll($query, $params);
    }

    protected function getQueryListUser($query, $params)
    {
        $this->filterExcludeNewsfeatured($query, $params);

        $query->andFilterWhere(['=', 'news.status', News::STATUS_ACTIVE]);

        return $this->getQueryAll($query, $params);
    }

    protected function getQueryAll($query, $params)
    {
        $pageLimit = Arr::get($params, 'limit');
        $sortBy    = Arr::get($params, 'sort_by', 'source_date');
        $sortOrder = Arr::get($params, 'sort_order', 'descending');
        $sortOrder = ModelHelper::getSortOrder($sortOrder);

        return new ActiveDataProvider([
            'query'      => $query,
            'sort'       => [
                'defaultOrder' => [$sortBy => $sortOrder],
                'attributes' => [
                    'title',
                    'source_date',
                    'total_viewers',
                    'seq',
                    'status',
                    'channel.name' => [
                        'asc' => ['news_channels.name' => SORT_ASC],
                        'desc' => ['news_channels.name' => SORT_DESC],
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
        $kabkotaId = Arr::get($params, 'kabkota_id');
        if ($kabkotaId) {
            if ($this->userRole == User::ROLE_STAFF_KABKOTA) {
                // staffKabkota dapat melihat berita Jabar dan berita wilayahnya
                $query->andWhere(['or',
                    ['kabkota_id' => $kabkotaId],
                    ['kabkota_id' => null]
                ]);
            } else {
                $query->andFilterWhere(['kabkota_id' => $kabkotaId]);
            }
        } else {
            if ($this->scenario === self::SCENARIO_LIST_USER) {
                $query->andWhere(['kabkota_id' => null]);
            }
        }
    }

    protected function filterExcludeNewsfeatured($query, $params)
    {
        $newsIds = [];
        $newsFeatured = NewsFeatured::find()->select('news_id')->asArray()->all();
        if (!empty($newsFeatured)) {
            foreach ($newsFeatured as $newsId) {
                $newsIds[] = $newsId['news_id'];
            }
            $query->andFilterWhere(['not in', 'news.id', $newsIds]);
        }
    }
}
