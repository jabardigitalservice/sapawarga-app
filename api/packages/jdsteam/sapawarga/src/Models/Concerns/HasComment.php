<?php

namespace Jdsteam\Sapawarga\Models\Concerns;

use app\models\User;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;

trait HasComment
{
    public function getAuthor()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /**
     * {@inheritdoc}
     */
    protected function rulesComment()
    {
        return [
            ['text', 'string', 'max' => 500],

            [['text'], 'trim'],
            [['text'], 'safe'],

            [['text', 'status'], 'required'],

            ['status', 'integer'],
            ['status', 'in', 'range' => [-1, 0, 10]],
        ];
    }

    protected function fieldsComment()
    {
        return [
            'id',
            'text',
            'user' => 'AuthorField',
            'created_at',
            'updated_at',
            'created_by',
            'updated_by',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'text' => 'Komentar',
            'status' => 'Status',
        ];
    }

    /** @inheritdoc */
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => time(),
            ],
            BlameableBehavior::class,
        ];
    }

    protected function getAuthorField()
    {
        $publicBaseUrl = Yii::$app->params['storagePublicBaseUrl'];

        return [
            'id' => $this->author->id,
            'name' => $this->author->name,
            'photo_url_full' => $this->author->photo_url ? "$publicBaseUrl/{$this->author->photo_url}" : null,
            'role_label' => $this->author->getRoleName(),
            'kabkota' => isset($this->author->kabkota->name) ? $this->author->kabkota->name : null,
            'kelurahan' => isset($this->author->kelurahan->name) ? $this->author->kelurahan->name : null,
            'kecamatan' => isset($this->author->kecamatan->name) ? $this->author->kecamatan->name : null,
            'rw' => isset($this->author->rw) ? $this->author->rw : null,
            'role_label' => $this->author->getRoleName(),
        ];
    }
}
