<?php

namespace app\models;

use app\components\ModelHelper;
use Illuminate\Support\Arr;
use yii\data\ActiveDataProvider;

/**
 * CategorySearch represents the model behind the search form of `app\models\Category`.
 */
class CategorySearch extends Category
{
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
        $sortOrder = ModelHelper::getSortOrder($sortOrder);

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

        $query->andFilterWhere(['<>', 'status', Category::STATUS_DELETED]);
        $query->andFilterWhere(['like', 'name', Arr::get($params, 'name')]);
        $query->andFilterWhere(['like', 'type', Arr::get($params, 'type')]);

        // Melakukan pengecualian terhadap tipe kategori 'newsHoax' dan 'notification'
        if (!Arr::has($params, 'type')) {
            $query->andFilterWhere(['not in', 'type', Category::EXCLUDED_TYPES]);
        }

        // Special case. For 'notification' Category type, only returns 'Update Aplikasi' and 'Lainnya' category name
        if (Arr::get($params, 'type') == Notification::CATEGORY_TYPE) {
            $query->andFilterWhere(['name' => Notification::CATEGORY_LABEL_UPDATE]);
        }

        if (Arr::get($params, 'type')) {
            $this->moveDefaultCategory($dataProvider);
        }

        return $dataProvider;
    }

    /**
     * Move default category ('Lainnya') to the last index of search results
     *
     * @param array $dataProvider
     *
     */
    public function moveDefaultCategory(&$dataProvider)
    {
        $models = $dataProvider->getModels();
        $idx = array_search(Category::DEFAULT_CATEGORY_NAME, array_column($models, 'name'));
        $splicedElement = array_splice($models, $idx, 1);
        array_push($models, $splicedElement[0]);
        $dataProvider->setModels($models);
    }
}
