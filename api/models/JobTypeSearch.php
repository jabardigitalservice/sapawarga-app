<?php

namespace app\models;

use app\components\ModelHelper;
use Illuminate\Support\Arr;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * JobTypeSearch represents the model behind the search form of `app\models\JobType`.
 */
class JobTypeSearch extends Model
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
        $query = JobType::find();

        return $this->getQueryAll($query, $params);
    }

    protected function getQueryAll($query, $params)
    {
        $pageLimit = Arr::get($params, 'limit');
        $sortBy    = Arr::get($params, 'sort_by', 'seq');
        $sortOrder = Arr::get($params, 'sort_order', 'ascending');
        $sortOrder = ModelHelper::getSortOrder($sortOrder);

        return new ActiveDataProvider([
            'query'      => $query,
            'sort'       => [
                'defaultOrder' => [$sortBy => $sortOrder],
            ],
            'pagination' => [
                'pageSize' => $pageLimit,
            ],
        ]);
    }
}
