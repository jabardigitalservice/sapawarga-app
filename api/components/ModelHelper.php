<?php

namespace app\components;

use Yii;
use Carbon\Carbon;
use Illuminate\Support\Arr;
use app\models\Category;
use app\models\Notification;

class ModelHelper
{
    /**
     * Checks if category_id is part of category_type
     *
     * @param $id
     * @param $params
     */
    public static function validateCategoryID($model, $attribute)
    {
        $request = Yii::$app->request;

        if ($request->isPost || $request->isPut) {
            $searched_category_id = Category::find()
                ->where(['id' => $model->$attribute])
                ->andWhere(['type' => $model::CATEGORY_TYPE]);

            if ($searched_category_id->count() <= 0) {
                $model->addError($attribute, Yii::t('app', 'error.id.invalid'));
            }
        }
    }

    /**
     * Checks if a new model needs to send a notification
     *
     * @param $insert
     * @param $changedAttributes
     * @param $model
     */
    public static function isSendNotification($insert, $changedAttributes, $model)
    {
        if (!YII_ENV_TEST) {
            if ($insert) { // Model is created
                return $model->status == $model::STATUS_PUBLISHED;
            } else { // Model is updated
                if (array_key_exists('status', $changedAttributes)) {
                    return $model->status == $model::STATUS_PUBLISHED;
                }
            }
        }
        return false;
    }

    /**
     * Create a new notification to mobile app, notifying new content
     *
     * @param $categoryName
     * @param $model
     * @param $meta
     */
    public static function sendNewContentNotification($categoryName, $title, $description, $target, $meta)
    {
        $category_id = Category::findOne(['name' => $categoryName])->id;
        $notifModel = new Notification();
        $notifModel->setAttributes([
            'category_id' => $category_id,
            'title'=> $title,
            'description'=> $description,
            'kabkota_id'=> Arr::get($target, 'kabkota_id', null),
            'kec_id'=> Arr::get($target, 'kec_id', null),
            'kel_id'=> Arr::get($target, 'kel_id', null),
            'rw'=> Arr::get($target, 'rw', null),
            'status'=> Notification::STATUS_PUBLISHED,
            'meta' => $meta,
        ]);
        $notifModel->save(false);
    }

    /**
     * Filters query with area ids provided in params
     *
     * @param &$query
     * @param $params
     */
    public static function filterByArea(&$query, $params)
    {
        if (Arr::has($params, 'kabkota_id')) {
            $query->andWhere(['or',
                ['kabkota_id' => $params['kabkota_id']],
                ['kabkota_id' => null]]);
        }

        if (Arr::has($params, 'kec_id')) {
            $query->andWhere(['or',
                ['kec_id' => $params['kec_id']],
                ['kec_id' => null]]);
        }

        if (Arr::has($params, 'kel_id')) {
            $query->andWhere(['or',
                ['kel_id' => $params['kel_id']],
                ['kel_id' => null]]);
        }

        if (Arr::has($params, 'rw')) {
            $query->andWhere(['or',
                ['rw' => $params['rw']],
                ['rw' => null]]);
        }

        return $query;
    }

    /**
     * Filters query by area ids, using 'top-down' approach
     *
     * @param &$query
     * @param $params
     */
    public static function filterByAreaTopDown(&$query, $params)
    {
        if (Arr::has($params, 'kabkota_id')) {
            $query->andFilterWhere(['kabkota_id' => $params['kabkota_id']]);
        }

        if (Arr::has($params, 'kec_id')) {
            $query->andFilterWhere(['kec_id' => $params['kec_id']]);
        }

        if (Arr::has($params, 'kel_id')) {
            $query->andFilterWhere(['kel_id' => $params['kel_id']]);
        }

        if (Arr::has($params, 'rw')) {
            $query->andFilterWhere(['rw' => $params['rw']]);
        }

        return $query;
    }


    public static function filterCurrentActiveNow(&$query, $model)
    {
        $query->andFilterWhere(['=', 'status', $model::STATUS_PUBLISHED]);

        $today = new Carbon();
        $query->andFilterWhere([
            'and',
            ['<=', 'start_date', $today->toDateString()],
            ['>=', 'end_date', $today->toDateString()]
        ]);

        return $query;
    }

    public static function filterIsEnded(&$query, $model)
    {
        $query->andFilterWhere(['=', 'status', $model::STATUS_PUBLISHED]);

        $today = new Carbon();
        $query->andFilterWhere(['<', 'end_date', $today->toDateString()]);

        return $query;
    }

    public static function getSortOrder($sortOrder)
    {
        switch ($sortOrder) {
            case 'descending':
                return SORT_DESC;
                break;
            case 'ascending':
            default:
                return SORT_ASC;
                break;
        }
    }

    public static function getLoggedInUserId() {
        return Yii::$app->user->getId();
    }
}
