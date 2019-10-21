<?php

namespace app\models;

use Illuminate\Support\Arr;
use yii\db\Query;
use Carbon\Carbon;
use Yii;

/**
 * NewsDashboard is model represents to collect news data in the dashboard.
 */
class NewsDashboard extends News
{
    /**
     * Creates data provider instance applied to get news most likes per province / kabkota
     *
     * @param string $location
     * @param string $start_date (optional)
     * @param string $end_date (optional)
     *
     * @return SqlDataProvider
     */

    public function getNewsMostLikes($params)
    {
        $publicBaseUrl = Yii::$app->params['storagePublicBaseUrl'] . '/';
        $today = Carbon::now();
        $lastTwoWeeks = Carbon::now()->subDays(14);
        $startDate = Arr::get($params, 'start_date', $lastTwoWeeks);
        $endDate = Arr::get($params, 'end_date', $today);
        $location = Arr::get($params, 'location');

        $query = new Query;
        $query->select([
                    'id',
                    'title',
                    "CONCAT('$publicBaseUrl', cover_path) AS cover_path_url",
                    'total_viewers',
            ])
            ->from('news')
            ->where(['=', 'status', News::STATUS_PUBLISHED])
            ->andWhere(['>', 'total_viewers', 0])
            ->orderBy(['total_viewers' => SORT_DESC])
            ->limit(5);

        // Filtering location
        if ($location == 'province') {
            $query->andWhere(['is', 'kabkota_id', null]);
        }
        if ($location == 'kabkota') {
            $query->andWhere(['is not', 'kabkota_id', null]);
        }

        // Filtering range date
        if (! empty($startDate) && ! empty($endDate)) {
            $query->andWhere([
                'and',
                ['>=', 'created_at', strtotime($startDate)],
                ['<=', 'created_at', strtotime($endDate)]
            ]);
        }

        return $query->all();
    }
}
