<?php

namespace app\modules\v1\repositories;

use app\models\NewsFeatured;
use Illuminate\Support\Arr;
use yii\db\Query;

class NewsFeaturedRepository
{
    public function getList($params = [])
    {
        $limit = 5;

        /**
         * @var Query $query
         */
        $query   = NewsFeatured::find();
        $query   = $this->getListBuildFilterQuery($query, $params);
        $records = $query->with('news')->limit($limit)->all();

        $rows = [];

        for ($n = 1; $n <= $limit; $n++) {
            $rows[] = $this->getListMatchSequence($records, $n);
        }

        return $rows;
    }

    protected function getListBuildFilterQuery(Query $query, $params = []): Query
    {
        $kabkotaId = Arr::get($params, 'kabkota_id');

        if ($kabkotaId !== null) {
            return $query->where(['kabkota_id' => $kabkotaId]);
        }

        return $query->where(['kabkota_id' => null]);
    }

    protected function getListMatchSequence($records, $n)
    {
        $record = Arr::first($records, function ($record) use ($n) {
            return $record->seq === $n;
        });

        if ($record !== null) {
            return $record;
        }

        return null;
    }

    public function resetFeatured($kabkotaId)
    {
        if ($kabkotaId === null) {
            return NewsFeatured::deleteAll('kabkota_id is null');
        }

        return NewsFeatured::deleteAll('kabkota_id = :kabkota_id', ['kabkota_id' => $kabkotaId]);
    }
}
