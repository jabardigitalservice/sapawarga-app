<?php

namespace app\models;

use Illuminate\Support\Arr;
use yii\data\SqlDataProvider;

/**
 * AspirasiDashboard represents the model behind the search form of `app\models\Aspirasi`.
 */
class AspirasiDashboard extends Aspirasi
{
    /**
     * Creates data provider instance applied for get aspirasi most likes
     *
     * @param array $paramsSql
     *
     * @return SqlDataProvider
     */

    public function getAspirasiMostLikes($params)
    {
        $conditional = '';
        $limit = Arr::get($params, 'limit');
        $paramsSql = [':status_active' => Aspirasi::STATUS_PUBLISHED];

        // Filtering
        $categoryId = Arr::get($params, 'category_id');
        if ($categoryId) {
            $conditional .= 'AND a.category_id = :category_id ';
            $paramsSql[':category_id'] = $categoryId;
        }

        $kabKotaId = Arr::get($params, 'kabkota_id');
        if ($kabKotaId) {
            $conditional .= 'AND a.kabkota_id = :kabkota_id ';
            $paramsSql[':kabkota_id'] = $kabKotaId;
        }

        // Query
        $sql = "SELECT a.id, title, cat.name AS category_name, a.category_id, COUNT(al.aspirasi_id) AS total_likes, kabkota_id, DATE_FORMAT(FROM_UNIXTIME(a.created_at), '%d %m %Y') AS created_at
                FROM aspirasi_likes al
                LEFT JOIN aspirasi a ON a.id = al.aspirasi_id
                LEFT JOIN categories cat ON cat.id = a.category_id
                WHERE a.status = :status_active
                $conditional
                GROUP BY aspirasi_id
                ORDER BY total_likes DESC";

        return new SqlDataProvider([
            'sql'      => $sql,
            'params'   => $paramsSql,
            'pagination' => [
                'pageSize' => $limit,
            ],
        ]);
    }

    /**
     * Creates data provider instance applied for get total aspirasi group by status
     *
     * @return SqlDataProvider
     */
    public function getAspirasiCounts($params)
    {
        // Query
        $sql = 'SELECT 	CASE
                    WHEN `status` = 3 THEN "STATUS_APPROVAL_REJECTED"
                    WHEN `status` = 5 THEN "STATUS_APPROVAL_PENDING"
                    WHEN `status` = 10 THEN "STATUS_PUBLISHED"
                    END as `status`, count(id) AS total_count
                FROM aspirasi
                WHERE `status` > :status_draft
                GROUP BY `status`
                ORDER BY `status`';

        $provider = new SqlDataProvider([
            'sql'      => $sql,
            'params'   => [':status_draft' => Aspirasi::STATUS_DRAFT],
        ]);

        $posts = $provider->getModels();

        $data = [];
        foreach ($posts as $value) {
            $data[$value['status']] = $value['total_count'];
        }

        return $data;
    }

    /**
     * Creates data provider instance applied for get total aspirasi group by kabkota
     *
     * @param array $paramsSql
     *
     * @return SqlDataProvider
     */
    public function getAspirasiGeo($params)
    {
        $conditional = '';
        $limit = Arr::get($params, 'limit');
        $paramsSql = [':status_active' => Aspirasi::STATUS_PUBLISHED];

        // Filtering
        $kabKotaId = Arr::get($params, 'kabkota_id');
        if ($kabKotaId) {
            $conditional .= 'AND a.kabkota_id = :kabkota_id ';
            $paramsSql[':kabkota_id'] = $kabKotaId;
        }

        // Query
        $sql = "SELECT areas.name, count(a.id) as counts, kabkota_id, latitude, longitude
                FROM sapawarga.aspirasi a
                LEFT JOIN areas ON areas.id = kabkota_id
                WHERE a.status = :status_active
                $conditional
                GROUP BY kabkota_id
                ORDER BY counts DESC";

        return new SqlDataProvider([
            'sql'      => $sql,
            'params'   => $paramsSql,
            'pagination' => [
                'pageSize' => $limit,
            ],
        ]);
    }
}