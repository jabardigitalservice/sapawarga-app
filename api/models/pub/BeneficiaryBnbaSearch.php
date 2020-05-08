<?php

namespace app\models\pub;

use app\components\ModelHelper;
use Illuminate\Support\Arr;
use yii\data\ActiveDataProvider;
use yii\data\SqlDataProvider;

/**
 * BeneficiaryBnbaSearch represents the model behind the search form of `app\models\pub\BeneficiaryBnba`.
 */
class BeneficiaryBnbaSearch extends BeneficiaryBnba
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
        $query = BeneficiaryBnba::find()->where('1=1');

        // Filtering
        $query->andFilterWhere(['id' => $this->id]);
        $query->andFilterWhere(['like', 'nama_krt', Arr::get($params, 'nama_krt')]);
        $query->andFilterWhere(['like', 'nik', Arr::get($params, 'nik')]);
        $query->andFilterWhere(['lapangan_usaha' => Arr::get($params, 'lapangan_usaha')]);
        $query->andFilterWhere(['rt' => Arr::get($params, 'rt')]);
        $query->andFilterWhere(['rw' => Arr::get($params, 'rw')]);
        $query->andFilterWhere(['id_tipe_bansos' => Arr::get($params, 'id_tipe_bansos')]);

        return $this->getQueryAll($query, $params);
    }

    protected function getQueryAll($query, $params)
    {
        $pageLimit = Arr::get($params, 'limit');
        $sortBy    = Arr::get($params, 'sort_by', 'nik');
        $sortOrder = Arr::get($params, 'sort_order', 'ascending');
        $sortOrder = ModelHelper::getSortOrder($sortOrder);

        return new ActiveDataProvider([
            'query'      => $query,
            'sort'       => [
                'defaultOrder' => [$sortBy => $sortOrder],
                'attributes' => [
                    'nama_krt',
                    'nik',
                    'lapangan_usaha',
                    'rt',
                    'rw',
                    'id_tipe_bansos',
                ],
            ],
            'pagination' => [
                'pageSize' => $pageLimit,
            ],
        ]);
    }
}
