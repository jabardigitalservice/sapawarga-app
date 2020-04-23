<?php

namespace app\models;

use Jdsteam\Sapawarga\Models\Contracts\ActiveStatus;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "releases".
 *
 * @property int $id
 * @property string $version
 * @property boolean $force_update
 * @property int $created_by
 * @property int $updated_by
 * @property int $created_at
 * @property int $updated_at
 */
class Release extends ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'releases';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['version', 'force_update'], 'required'],
            ['version', 'string'],
            ['version', 'trim'],
            ['version', 'unique'],
            [
                'version',
                'match',
                'pattern' => '/^(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)$/'
            ],
            ['force_update', 'boolean'],
        ];
    }

    public function fields()
    {
        $fields = parent::fields();

        $fields['force_update'] = function () {
            return (bool) $this->force_update;
        };

        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'version' => 'Versi',
            'force_update' => 'Force Update',
        ];
    }

    /** @inheritdoc */
    public function behaviors()
    {
        return [
            [
                'class'              => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value'              => time(),
            ],
            BlameableBehavior::class,
        ];
    }

    public function afterSave($insert, $changedAttributes)
    {
        $this->createManifest($this);

        return parent::afterSave($insert, $changedAttributes);
    }

    public function afterDelete()
    {
        $latest = Release::find()->orderBy(['id' => SORT_DESC])->one();

        $this->createManifest($latest);

        return parent::afterDelete();
    }

    public function createManifest(Release $latest): void
    {
        $json = json_encode([
            'version' => $latest->version,
            'force_update' => $latest->force_update,
        ]);

        file_put_contents(__DIR__ . '/../web/assets/version.json', $json);
    }
}
