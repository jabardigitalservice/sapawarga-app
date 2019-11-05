<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "news_important_attachment".
 *
 * @property int $id
 * @property int $news_important_id
 * @property string $file_path
 */

class NewsImportantAttachment extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'news_important_attachment';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['name', 'string', 'min' => 5],
            ['name', 'string', 'max' => 25],
            [['name'], 'unique'],
            [['name', 'icon_url'], 'trim'],
            [['name', 'icon_url'], 'safe'],

            [['id', 'news_important_id', 'file_path'], 'required'],

            ['status', 'integer'],
        ];
    }

    public function fields()
    {
        $fields = [
            'id',
            'news_important_id',
            'file_path',
        ];

        return $fields;
    }
}
