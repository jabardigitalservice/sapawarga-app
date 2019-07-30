<?php

namespace app\models;

use app\validator\InputCleanValidator;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "video".
 *
 * @property int $id
 * @property string $title
 * @property int $category_id
 * @property string $source
 * @property string $video_url
 * @property int $kabkota_id
 * @property int $total_likes
 * @property int $seq
 * @property int $status
 * @property int $created_by
 * @property int $updated_by
 * @property int $created_at
 * @property int $updated_at
 */

class Video extends ActiveRecord
{
    const STATUS_DELETED = -1;
    const STATUS_DISABLED = 0;
    const STATUS_ACTIVE = 10;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'videos';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['title', 'string', 'max' => 100],
            ['title', 'string', 'min' => 10],
            ['title', InputCleanValidator::class],
            ['title', 'trim'],
            [['source','title', 'video_url'], 'safe'],
            [
                ['title', 'category_id', 'source', 'video_url', 'status'],
                'required'
            ],
            [
                ['category_id', 'kabkota_id', 'status','seq'],
                 'integer'
            ],
            ['source', 'in', 'range' => ['youtube']],
            ['status', 'in', 'range' => [-1, 0, 10]],
        ];
    }

    public function fields()
    {
        $fields = [
            'id',
            'title',
            'category_id',
            'source',
            'video_url',
            'kabkota_id',
            'total_likes',
            'seq',
            'status',
            'status_label' => function () {
                return $this->getStatusLabel();
            },
            'is_user_like' => function () {
                return $this->getIsUserLikes($this->id);
            },
            'created_at',
            'updated_at',
        ];

        return $fields;
    }

    protected function getIsUserLikes($id)
    {
        $isUserLikes = false;

        $userId = Yii::$app->user->getId();

        $checkExistUserLike = Like::find()
                ->where(['type' => Like::TYPE_VIDEO, 'entity_id' => $id, 'user_id' => $userId])
                ->one();

        if (! empty($checkExistUserLike)) {
            $isUserLikes = true;
        }

        return $isUserLikes;
    }

    protected function getStatusLabel()
    {
        $statusLabel = '';

        switch ($this->status) {
            case self::STATUS_ACTIVE:
                $statusLabel = Yii::t('app', 'status.active');
                break;
            case self::STATUS_DISABLED:
                $statusLabel = Yii::t('app', 'status.inactive');
                break;
            case self::STATUS_DELETED:
                $statusLabel = Yii::t('app', 'status.deleted');
                break;
        }

        return $statusLabel;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Judul',
            'category_id' => 'Kategori',
            'source' => 'Sumber Video',
            'url' => 'URL',
            'kabkota_id' => 'KAB / KOTA',
            'seq' => 'Proiritas',
            'status' => 'Status',
        ];
    }

    /** @inheritdoc */
    public function behaviors()
    {
        return [
            [
                'class'              => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value'              => time(),
            ],
            BlameableBehavior::class,
        ];
    }
}
