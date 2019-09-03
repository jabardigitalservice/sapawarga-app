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
     * Creates data provider instance applied for get total and count percentage aspirasi per id
     *
     * @param array $paramsSql
     *
     * @return SqlDataProvider
     */
    public function getPollingChart($params)
    {
        $paramsSql[':polling_id'] = Arr::get($params, 'id');

        $sql = 'SELECT pa.polling_id, pa.id AS answer_id, pa.body AS answer_body, ifnull(vote.votes, 0) as votes FROM polling_answers pa
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
