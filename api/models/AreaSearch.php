<?php

namespace app\models;

use Illuminate\Support\Arr;
use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * AreaSearch represents the model behind the search form of `app\models\Area`.
 */
class AreaSearch extends Area
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['name'], 'safe'],
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
        $query = Area::find();

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

        // var_dump($this->load($params)); exit; @TODO parameter tidak terload ke model ?

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
        ]);

        $query->andFilterWhere(['code_bps' => $params['code_bps'] ?? null]);
        $query->andFilterWhere(['parent_id' => $params['parent_id'] ?? null]);
        $query->andFilterWhere(['depth' => $params['depth'] ?? null]);
        $query->andFilterWhere(['like', 'name', $params['name'] ?? null]);

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
