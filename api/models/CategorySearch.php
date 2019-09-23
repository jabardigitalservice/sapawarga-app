<?php

namespace app\models;

use Illuminate\Support\Arr;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * CategorySearch represents the model behind the search form of `app\models\Category`.
 */
class CategorySearch extends Category
{
    const SCENARIO_LIST_NON_SABERHOAX = 'list-non-saberhoax';

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['name', 'type'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        $scenarios = Model::scenarios();
        $scenarios[self::SCENARIO_LIST_NON_SABERHOAX] = ['id', 'name', 'type'];
        return $scenarios;
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
        $query = Category::find();

        // add conditions that should always apply here

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

        $query->andFilterWhere(['like', 'name', Arr::get($params, 'name')]);
        $query->andFilterWhere(['like', 'type', Arr::get($params, 'type')]);

        if ($this->scenario === self::SCENARIO_LIST_NON_SABERHOAX) {
            $query->andFilterWhere(['<>', 'type', NewsHoax::CATEGORY_TYPE]);
        }

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
