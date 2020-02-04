<?php

namespace app\models;

use Illuminate\Support\Arr;
use yii\data\ActiveDataProvider;
use app\components\ModelHelper;

/**
 * GamificationMyBadgeSearch represents the model behind the search form of `app\models\GamificationParticipant`.
 */
class GamificationMyBadgeSearch extends GamificationParticipant
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
        $query = GamificationParticipant::find()
                ->joinWith('gamification', '`gamification`.`id` = `gamification_participants`.`gamification_id`')
                ->where(['gamifications.status' => Gamification::STATUS_ACTIVE])
                ->andWhere(['user_id' => $params['user_id']])
                ->andWhere('total_hit = total_user_hit');

        return $this->createActiveDataProvider($query, $params);
    }

    protected function createActiveDataProvider($query, $params)
    {
        $pageLimit = false;
        $sortBy    = Arr::get($params, 'sort_by', 'created_at');
        $sortOrder = Arr::get($params, 'sort_order', 'descending');
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
