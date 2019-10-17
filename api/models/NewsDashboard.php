<?php

namespace app\models;

use Illuminate\Support\Arr;
use yii\db\Query;
use Carbon\Carbon;
use Yii;


/**
 * NewsDashboard represents the model behind the search form of `app\models\News`.
 */
class NewsDashboard extends News
{
    /**
     * Creates data provider instance applied for get news most likes
     *
     * @param array $paramsSql
     *
     * @return SqlDataProvider
     */

    public function getNewsMostLikes($params)
    {
        $lastTwoWeeks = Carbon::now()->subDays(14)->toDateTimeString();
        $publicBaseUrl = Yii::$app->params['storagePublicBaseUrl'] . '/';
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
            ->andWhere(['>=', 'created_at', $lastTwoWeeks])
            ->limit(5);

        // Filtering
        if ($location == 'province') {
            $query->andWhere(['is', 'kabkota_id', null]);
        }

        if ($location == 'kabkota') {
            $query->andWhere(['is not', 'kabkota_id', null]);
        }

        return $query->all();
    }
}
