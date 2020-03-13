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
     * @param array $params['limit'] Limit result data, default is 10
     * @param array $params['kabkota_id'] Limit result data
     *
     * @return ActiveDataProvider
     */
    public function getPollingLatest($params)
    {
        $paramsSql[':status_published'] = Polling::STATUS_PUBLISHED;
        $limit = Arr::get($params, 'limit', 10);

        // Conditional for admin and staffprov
        $conditional = 'AND u.role = ' . User::ROLE_STAFF_PROV;
        if (Arr::get($params, 'kabkota_id') != null) {
            $paramsSql[':kabkota_id'] = Arr::get($params, 'kabkota_id');
            $conditional = 'AND (p.kabkota_id = :kabkota_id OR p.kabkota_id IS NULL ) ';
        }

        $voteSubquery = 'SELECT pa.id AS polling_answers_id, count(pv.id) AS votes
                        FROM polling_votes pv
                        LEFT JOIN polling_answers pa ON pa.id = pv.answer_id
                        GROUP BY answer_id';

        $pollingResultSubquery =
        "SELECT pa.polling_id,
                ifnull(vote.votes, 0) AS votes_count,
                JSON_OBJECT(
                    'answer_id', pa.id,
                    'answer_body', pa.body,
                    'votes', ifnull(vote.votes, 0)
                ) AS result
                FROM polling_answers pa
	    LEFT JOIN ($voteSubquery) AS vote ON vote.polling_answers_id = pa.id
	    ORDER BY pa.id ASC";

        $sql = "SELECT p.id, p.category_id, c.name AS category_name, p.name, p.question, p.start_date, p.end_date, p.status,
                       SUM(pr.votes_count) AS votes_count,
                       JSON_ARRAYAGG(pr.result) AS results
                FROM polling p
                LEFT JOIN categories c ON c.id = p.category_id
                LEFT JOIN user u ON u.id = p.created_by
                LEFT JOIN ($pollingResultSubquery) AS pr ON p.id = pr.polling_id
                WHERE p.status = :status_published
                $conditional
                GROUP BY p.id
                ORDER BY p.created_at DESC";

        $provider = new SqlDataProvider([
            'sql' => $sql,
            'params' => $paramsSql,
            'pagination' => [
                'pageSize' => $limit,
            ],
        ]);

        // Typecasts values in 'votes_count' and 'results'
        $pollings = $provider->getModels();
        foreach ($pollings as &$polling) {
            $polling['votes_count'] = intval($polling['votes_count']);
            $polling['results'] = json_decode($polling['results'], true);
        }

        return $pollings;
    }

    /**
     * Creates data provider instance applied for get polling result per id
     *
     * @param array $params['id'] Id of polling
     *
     * @return array
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

    /**
     * Gets number of published pollings, whose status is either 'ongoing' or 'ended'
     *
     * @param array $params['kabkota_id'] kabkota_id value, if user's role is staffKabkota
     *
     * @return SqlDataProvider
     */
    public function getPollingCounts($params)
    {
        $conditional = '';
        $paramsSql = [':status_disabled' => Polling::STATUS_DISABLED];

        $kabkotaId = Arr::get($params, 'kabkota_id');
        if ($kabkotaId != null) {
            $conditional .= 'AND kabkota_id = :kabkota_id ';
            $paramsSql[':kabkota_id'] = $kabkotaId;
        }

        $sql = "SELECT CASE
                    WHEN `status` = 10 THEN 'STATUS_PUBLISHED'
                    END as `status`, count(id) AS total_count
                FROM polling WHERE `status` > :status_disabled
                $conditional
                GROUP BY `status` ORDER BY `status`";

        $provider = new SqlDataProvider([
            'sql'      => $sql,
            'params'   => $paramsSql,
        ]);
        $posts = $provider->getModels();

        $data = [];
        foreach ($posts as $value) {
            $data[$value['status']] = $value['total_count'];
        }

        return $data;
    }

    /**
     * Gets polling participation rate, which is the percentage of unique staffRW and users who have voted in any polling
     *
     * @param array $params['kabkota_id'] kabkota_id value, if user's role is staffKabkota
     *
     * @return SqlDataProvider
     */
    public function getPollingParticipation($params)
    {
        $conditional = '';
        $paramsSql = [
            ':status_active' => User::STATUS_ACTIVE,
            ':role_rw' => User::ROLE_STAFF_RW,
            ':role_user' => User::ROLE_USER,
        ];

        $kabkotaId = Arr::get($params, 'kabkota_id');
        if ($kabkotaId != null) {
            $conditional .= 'AND user.kabkota_id = :kabkota_id ';
            $paramsSql[':kabkota_id'] = $kabkotaId;
        }

        $sql = "SELECT COUNT(DISTINCT polling_votes.user_id) as 'unique_voters',
                       COUNT(DISTINCT user.id) as 'active_users'
                FROM polling_votes, user
                WHERE user.last_login_at IS NOT NULL
                  AND user.status = :status_active
                  $conditional
                  AND (user.role = :role_rw OR user.role = :role_user)";

        $provider = new SqlDataProvider([
            'sql'      => $sql,
            'params'   => $paramsSql,
        ]);
        $result = $provider->getModels();

        $uniqueVoters = $result[0]['unique_voters'];
        $activeUsers = $result[0]['active_users'];
        $pollingParticipation = 0;
        if ($activeUsers > 0) {
            $pollingParticipation = round($uniqueVoters / $activeUsers * 100, 2);
        }
        $data = [ 'polling_participation' =>  $pollingParticipation . '%'];

        return $data;
    }
}
