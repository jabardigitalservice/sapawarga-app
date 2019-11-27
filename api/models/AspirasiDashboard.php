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
     * @param array $params['limit'] Limit result data
     * @param array $params['category_id'] Filtering by category_id
     * @param array $params['kabkota_id'] Filtering by kabkota_id
     *
     * @return SqlDataProvider
     */

    public function getAspirasiMostLikes($params)
    {
        $conditional = '';
        $limit = Arr::get($params, 'limit');
        $paramsSql = [':status_active' => Aspirasi::STATUS_PUBLISHED];

        // Filtering
        if (Arr::get($params, 'category_id') != null) {
            $conditional .= 'AND a.category_id = :category_id ';
            $paramsSql[':category_id'] = Arr::get($params, 'category_id');
        }

        if (Arr::get($params, 'kabkota_id') != null) {
            $conditional .= 'AND a.kabkota_id = :kabkota_id ';
            $paramsSql[':kabkota_id'] = Arr::get($params, 'kabkota_id');
        }

        $sql = "SELECT a.id, title, cat.name AS category_name, a.category_id, COUNT(al.aspirasi_id) AS total_likes, kabkota_id, DATE_FORMAT(FROM_UNIXTIME(a.created_at), '%d %m %Y') AS created_at
                FROM aspirasi_likes al
                LEFT JOIN aspirasi a ON a.id = al.aspirasi_id
                LEFT JOIN categories cat ON cat.id = a.category_id
                WHERE a.status = :status_active
                $conditional
                GROUP BY aspirasi_id
                ORDER BY total_likes DESC";

        $provider = $this->getSqlDataProvider($sql, $paramsSql, $limit);

        return $provider;
    }

    /**
     * Creates data provider instance applied for get total aspirasi group by status
     *
     * @param array $params['kabkota_id'] Filtering by kabkota_id
     *
     * @return SqlDataProvider
     */
    public function getAspirasiCounts($params)
    {
        $conditional = '';
        $paramsSql = [':status_draft' => Aspirasi::STATUS_DRAFT];

        if (Arr::get($params, 'kabkota_id') != null) {
            $conditional .= 'AND kabkota_id = :kabkota_id ';
            $paramsSql[':kabkota_id'] = Arr::get($params, 'kabkota_id');
        }

        $sql = "SELECT CASE
                    WHEN `status` = 3 THEN 'STATUS_APPROVAL_REJECTED'
                    WHEN `status` = 5 THEN 'STATUS_APPROVAL_PENDING'
                    WHEN `status` = 7 THEN 'STATUS_UNPUBLISHED'
                    WHEN `status` = 10 THEN 'STATUS_PUBLISHED'
                    END as `status`, count(id) AS total_count
                FROM aspirasi WHERE `status` > :status_draft
                $conditional
                GROUP BY `status`
                ORDER BY `status`";

        $provider = $this->getSqlDataProvider($sql, $paramsSql);

        $posts = $provider->getModels();

        $data = [];
        foreach ($posts as $value) {
            $data[$value['status']] = $value['total_count'];
        }

        return $data;
    }

    /**
     * Creates data provider instance applied for get total aspirasi per category
     *
     * @param array $params['limit'] Limit result data
     * @param array $params['kabkota_id'] Filtering by kabkota_id
     *
     * @return SqlDataProvider
     */
    public function getAspirasiCategoryCounts($params)
    {
        $conditional = '';
        $limit = (int) Arr::get($params, 'limit', 20);
        $paramsSql = [':status_draft' => Aspirasi::STATUS_DRAFT, ':limit' => $limit];

        if (Arr::get($params, 'kabkota_id') != null) {
            $conditional .= 'AND kabkota_id = :kabkota_id ';
            $paramsSql[':kabkota_id'] = Arr::get($params, 'kabkota_id');
        }

        $sql = "SELECT c.name, count(a.id) as total
                FROM aspirasi a
                LEFT JOIN categories c ON c.id = a.category_id
                WHERE a.status > :status_draft
                $conditional
                GROUP BY category_id
                ORDER BY total DESC
                LIMIT :limit";

        $provider = $this->getSqlDataProvider($sql, $paramsSql);

        return $provider->getModels();
    }

    /**
     * Creates data provider instance applied for get total aspirasi group by kabkota
     *
     * @param array $params['limit'] Limit result data
     * @param array $params['kabkota_id'] Filtering by kabkota_id
     * @param array $params['kec_id'] Filtering by kec_id
     *
     * @return SqlDataProvider
     */
    public function getAspirasiGeo($params)
    {
        $conditional = '';
        $limit = Arr::get($params, 'limit');
        $paramsSql = [':status_active' => Aspirasi::STATUS_PUBLISHED, ':parent_id' => 1];
        $groupBy = 'kabkota_id';

        // Filtering
        if (Arr::get($params, 'kabkota_id') != null) {
            $paramsSql[':parent_id'] = Arr::get($params, 'kabkota_id');
            $groupBy = 'kec_id';
        }

        if (Arr::get($params, 'kec_id') != null) {
            $paramsSql[':parent_id'] = Arr::get($params, 'kec_id');
            $groupBy = 'kel_id';
        }

        $sql = "SELECT id, name, longitude, latitude, IFnull(aspirasi.counts, 0) AS counts
                FROM areas
                LEFT JOIN(
                    SELECT COUNT(a.id) AS counts, $groupBy AS area_id
                    FROM aspirasi a
                    WHERE a.status = :status_active
                    GROUP BY $groupBy
                ) AS aspirasi ON aspirasi.area_id = areas.id
                WHERE parent_id = :parent_id
                $conditional
                ORDER BY aspirasi.counts desc, name asc";

        $provider = $this->getSqlDataProvider($sql, $paramsSql);

        return $provider->getModels();
    }

    /**
     * Get SqlDataProvider
     *
     * @param array $sql Query for get data
     * @param array $paramsSql Query parameter of sql
     * @param array $limit Limit result, default is 0
     *
     * @return SqlDataProvider
     */
    public function getSqlDataProvider($sql, $paramsSql, $limit = 0)
    {
        $pagination = false;
        if ($limit > 0) {
            $pagination['pageSize'] = $limit;
        }

        $provider =  new SqlDataProvider([
            'sql' => $sql,
            'params' => $paramsSql,
            'pagination' => $pagination
        ]);

        return $provider;
    }
}
