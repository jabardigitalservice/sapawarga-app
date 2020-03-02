<?php

namespace app\models;

use Jdsteam\Sapawarga\Models\Concerns\HasActiveStatus;
use Jdsteam\Sapawarga\Models\Concerns\HasComment;
use Jdsteam\Sapawarga\Models\Contracts\ActiveStatus;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "news_important_comments".
 *
 * @property int $id
 * @property int $news_important_id
 * @property string $text
 * @property int $status
 * @property int $created_by
 * @property int $updated_by
 * @property int $created_at
 * @property int $updated_by
 */
class NewsImportantComment extends ActiveRecord implements ActiveStatus
{
    use HasActiveStatus, HasComment;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'news_important_comments';
    }

    public function getNewsImportant()
    {
        return $this->hasOne(NewsImportant::class, ['id' => 'news_important_id']);
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        $rules = [
            ['news_important_id', 'required'],
        ];

        return array_merge(
            $rules,
            $this->rulesComment()
        );
    }

    public function fields()
    {
        $fields = [
            'news_important_id',
        ];

        return array_merge(
            $fields,
            $this->fieldsComment()
        );
    }
}
