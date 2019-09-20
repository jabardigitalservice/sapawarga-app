<?php

namespace app\models;

use Illuminate\Support\Arr;
use yii\data\SqlDataProvider;

/**
 * DashboardPolling represents the model behind the search form of `app\models\Polling`.
 */
class DashboardPolling extends Polling
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
        $paramsSql[':role_staff_prov'] = User::ROLE_STAFF_PROV;
        $limit = Arr::get($params, 'limit', 10);

        $sql = 'SELECT p.id, p.category_id, c.name AS category_name, p.name, p.question, p.start_date, p.end_date, p.status
                FROM polling p
                LEFT JOIN categories c ON c.id = p.category_id
                LEFT JOIN user u ON u.id = p.created_by
                WHERE p.status = :status_published
                AND u.role = :role_staff_prov
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

    /**
     * Creates data provider instance applied for get polling result per id
     *
     * @param array $paramsSql
     *
     * @return SqlDataProvider
     */
    public function getPollingResult($params)
    {
        $paramsSql[':polling_id'] = Arr::get($params, 'id');

        $sql = 'SELECT pa.id AS answer_id, pa.body AS answer_body, ifnull(vote.votes, 0) as votes FROM polling_answers pa
                LEFT JOIN (SELECT pa.id as polling_answers_id, body, count(pv.id) AS votes
                                FROM polling_votes pv
                                LEFT JOIN polling_answers pa ON pa.id = pv.answer_id
                                WHERE pv.polling_id = :polling_id
                                GROUP BY answer_id
                ) as vote ON vote.polling_answers_id = pa.id
                WHERE pa.polling_id = :polling_id
                ORDER BY pa.id ASC';

        $provider = new SqlDataProvider([
            'sql'      => $sql,
            'params'   => $paramsSql,
        ]);

        return $provider->getModels();
    }
}
