<?php

namespace app\models;

use Jdsteam\Sapawarga\Models\Concerns\HasActiveStatus;
use Jdsteam\Sapawarga\Models\Contracts\ActiveStatus;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "categories".
 *
 * @property int $id
 * @property string $type
 * @property string $name
 * @property array $meta
 * @property int $status
 */
class Category extends ActiveRecord implements ActiveStatus
{
    use HasActiveStatus;

    // Memetakan category type id ke category type name
    const TYPE_MAP = [
        Aspirasi::CATEGORY_TYPE     => 'Usulan Masyarakat',
        Broadcast::CATEGORY_TYPE    => 'Pesan',
        Notification::CATEGORY_TYPE => 'Notifikasi',
        PhoneBook::CATEGORY_TYPE    => 'Nomor Penting',
        Polling::CATEGORY_TYPE      => 'Polling',
        Survey::CATEGORY_TYPE       => 'Survei',
        Video::CATEGORY_TYPE        => 'Video',
        NewsHoax::CATEGORY_TYPE     => 'Berita Saber Hoaks',
    ];

    // Daftar category type yang tidak bisa diedit oleh staff
    const TYPE_VIEW_ONLY = [
        Notification::CATEGORY_TYPE,
        NewsHoax::CATEGORY_TYPE,
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'categories';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['type', 'name'], 'string', 'max' => 64],
            [['type', 'name'], 'trim'],
            [['name', 'meta'], 'safe'],
            [['type', 'name', 'status'], 'required'],
            ['name', 'validateName'],
            ['status', 'integer'],
        ];
    }

    public function fields()
    {
        $fields = [
            'id',
            'type',
            'name',
            'meta',
            'status',
            'status_label' => 'StatusLabel',
            'created_at',
            'updated_at',
        ];

        return $fields;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'name' => 'Name',
            'meta' => 'Meta',
            'status' => 'Status',
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
        ];
    }

    /**
     * Checks if category name has been taken
     *
     * @param $attribute
     * @param $params
     */
    public function validateName($attribute, $params)
    {
        // get post type - POST or PUT
        // @TODO seharusnya tidak boleh ada request context di model
        $request = Yii::$app->request;

        if ($request->isPost || $request->isPut) {
            $existingName = Category::find()
                ->where(['name' => $this->$attribute])
                ->andWhere(['type' => $this->type]);

            return $this->validateNameCreateOrUpdate($request, $existingName, $attribute);
        }
    }

    protected function validateNameCreateOrUpdate($request, ActiveQuery $existingName, $attribute)
    {
        if ($request->isPut) {
            $existingName->andWhere(['!=', 'id', $this->id]);
        }

        return $this->returnError($existingName, $attribute);
    }

    protected function returnError(ActiveQuery $existingName, $attribute)
    {
        $existingName = $existingName->count();

        if ($existingName > 0) {
            $this->addError($attribute, Yii::t('app', 'error.category.taken'));
        }
    }
}
