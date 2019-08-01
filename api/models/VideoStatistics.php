<?php

namespace app\models;

use Illuminate\Support\Arr;
use yii\data\SqlDataProvider;
use app\components\ModelHelper;

/**
 * VideoStatistics represents the model behind the search form of `app\models\Video`.
 */
class VideoStatistics extends Video
{
    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return SqlDataProvider
     */

    public function getStatistics($params)
    {
        $query = 'SELECT c.id, c.name, IFNULL(count, 0) AS count FROM categories c
                    LEFT JOIN (
                        SELECT cat.id, cat.name, count(vid.id) AS count
                        FROM videos vid
                        LEFT JOIN categories cat ON vid.category_id = cat.id
                        WHERE vid.status <> :status_deleted
                        GROUP BY cat.id
                    ) AS statistic
                    ON c.id = statistic.id
                    WHERE c.type = :video_type
                    AND c.status <> :status_deleted';

        $sortBy    = Arr::get($params, 'sort_by', 'name');
        $sortOrder = Arr::get($params, 'sort_order', 'ascending');
        $sortOrder = ModelHelper::getSortOrder($sortOrder);

        return new SqlDataProvider([
            'sql' => $query,
            'params' => [
                            ':status_deleted' => Video::STATUS_DELETED,
                            ':video_type' => Video::CATEGORY_TYPE
                        ],
            'sort' => [
                'defaultOrder' => [$sortBy => $sortOrder],
                'attributes' => [
                    'id',
                    'name',
                    'count',
                ],
            ],
        ]);
    }
}
