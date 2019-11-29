<?php

namespace app\models;

use yii\db\ActiveRecord;

class Question extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'releases'; // TODO
    }
}
