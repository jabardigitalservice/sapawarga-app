<?php

namespace app\models;

use Illuminate\Support\Arr;
use yii\data\SqlDataProvider;

/**
 * PollingDashboard represents the model behind the search form of `app\models\Polling`.
 */
class PollingDashboard extends Polling
{
    /**
     * Creates data provider instance applied for get polling latest
     *
     * @param array $paramsSql
     *
     * @return ActiveDataProvider
     */
    public function getPollingLatest($params)
    {
        $paramsSql[':status_published'] = Polling::STATUS_PUBLISHED;
        $limit = Arr::get($params, 'limit', 10);

        $sql = 'SELECT p.id, p.category_id, c.name AS category_name, p.name, p.question, p.status
                FROM polling p
                LEFT JOIN categories c ON c.id = p.category_id
                WHERE p.status = :status_published
                ORDER BY p.created_at DESC';

        $provider = new SqlDataProvider([
            'sql'      => $sql,
            'params'   => $paramsSql,
            'pagination' => [
                'pageSize' => $limit,
            ],
        ]);

        return $provider->getModels();
    }
}
