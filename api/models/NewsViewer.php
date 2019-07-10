<?php

namespace app\models;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "news_viewers".
 *
 * @property int $id
 * @property int $news_id
 * @property int $user_id
 * @property int $read_count
 */
class NewsViewer extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'news_viewers';
    }
}
