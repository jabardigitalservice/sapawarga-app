<?php

namespace app\models;

use Illuminate\Support\Arr;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * NewsChannelSearch represents the model behind the search form of `app\models\NewsChannel`.
 */
class NewsChannelSearch extends NewsChannel
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'status'], 'integer'],
            [['name', 'website'], 'safe'],
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
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = NewsChannel::find();

        $searchKeyword   = Arr::get($params, 'search');
        $sortBy    = Arr::get($params, 'sort_by', 'name');
        $sortOrder = Arr::get($params, 'sort_order', 'ascending');
        $sortOrder = $this->getSortOrder($sortOrder);

        $pageLimit = Arr::get($params, 'limit');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'=> ['defaultOrder' => [$sortBy => $sortOrder]],
            'pagination' => [
                'pageSize' => $pageLimit,
            ],
        ]);

        if (isset($params['all']) && $params['all'] == true) {
            $dataProvider->setPagination(false);
        }

        if (!$this->validate()) {
            return $dataProvider;
        }

        if ($searchKeyword) {
            $query->andFilterWhere(['like', 'name', $searchKeyword]);
            $query->orFilterWhere(['like', 'website', $searchKeyword]);
        }

        $query->andFilterWhere(['<>', 'status', NewsChannel::STATUS_DELETED]);

        return $dataProvider;
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
