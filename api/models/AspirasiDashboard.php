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
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return SqlDataProvider
     */

    public function getAspirasiTop($params)
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
}
