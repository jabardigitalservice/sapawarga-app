<?php

namespace app\models;

use Illuminate\Support\Arr;
use yii\data\ActiveDataProvider;
use app\components\ModelHelper;

/**
 * GamificationParticipantSearch represents the model behind the search form of `app\models\GamificationParticipant`.
 */
class GamificationParticipantSearch extends GamificationParticipant
{
    const SCENARIO_LIST_USER = 'list-user';
    const SCENARIO_LIST_STAFF = 'list-staff';

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = GamificationParticipant::find()
                ->joinWith('gamification', '`gamification`.`id` = `gamification_participants`.`gamification_id`')
                ->where(['<>', 'gamifications.status', Gamification::STATUS_DELETED]);

        if ($this->scenario === self::SCENARIO_LIST_USER) {
            return $this->getQueryListUser($query, $params);
        } else {
            return $this->getQueryListStaff($query, $params);
        }
    }

    protected function getQueryListUser($query, $params)
    {
        $today = date('Y-m-d');

        if (isset($params['old'])) {
            $query->andwhere(['or', ['<','end_date', $today], 'total_user_hit = total_hit']);
        } else {
            $query->andWhere('total_user_hit <> total_hit');
            $query->andwhere(['and', ['<=','start_date', $today],['>=','end_date', $today]]);
        }

        $query->andFilterWhere(['user_id' => Arr::get($params, 'user_id')]);

        return $this->createActiveDataProvider($query, $params);
    }

    protected function getQueryListStaff($query, $params)
    {
        $query->andFilterWhere(['gamification_id' => Arr::get($params, 'id')]);

        return $this->createActiveDataProvider($query, $params);
    }

    protected function createActiveDataProvider($query, $params)
    {
        $pageLimit = Arr::get($params, 'limit');
        $sortBy    = Arr::get($params, 'sort_by', 'created_at');
        $sortOrder = Arr::get($params, 'sort_order', 'ascending');
        $sortOrder = ModelHelper::getSortOrder($sortOrder);

        $provider = new ActiveDataProvider([
            'query'      => $query,
            'sort'       => [
                'defaultOrder' => [$sortBy => $sortOrder],
                'attributes' => [
                    'start_date',
                    'created_at',
                ],
            ],
            'pagination' => [
                'pageSize' => $pageLimit,
            ],
        ]);

        return $provider;
    }
}
