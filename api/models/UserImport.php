<?php

namespace app\models;

use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Yii;
use yii\base\Model;

/**
 * User Import
 */
class UserImport extends Model
{
    public $username;
    public $password;
    public $email;
    public $name;
    public $phone;
    public $address;
    public $rt;
    public $rw;
    public $kel_id;
    public $kec_id;
    public $kabkota_id;
    public $role;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $rules = [
            ['password', 'string', 'length' => [5, User::MAX_LENGTH]],
            [['name', 'address'], 'string', 'max' => User::MAX_LENGTH],
            ['phone', 'string', 'length' => [3, 15]],

            [['username', 'name', 'email', 'password', 'role'], 'required'],

            [['role', 'kabkota_id', 'kec_id', 'kel_id', 'rw', 'rt'], 'default'],

            ['kabkota_id', 'required', 'when' => function ($model) {
                return $model->role <= User::ROLE_STAFF_KABKOTA;
            }, 'message' => 'Nama Kabupaten/Kota tidak diisi atau tidak ditemukan.'
            ],
            ['kabkota_id', 'validateKabkota'],

            ['kec_id', 'required', 'when' => function ($model) {
                return $model->role <= User::ROLE_STAFF_KEC;
            }, 'message' => 'Nama Kecamatan tidak diisi atau tidak ditemukan.'
            ],
            ['kec_id', 'validateKecamatan'],

            ['kel_id', 'required', 'when' => function ($model) {
                return $model->role <= User::ROLE_STAFF_KEL;
            }, 'message' => 'Nama Kelurahan tidak diisi atau tidak ditemukan.'
            ],
            ['kel_id', 'validateKelurahan'],

            ['rw', 'required', 'when' => function ($model) {
                return $model->role <= User::ROLE_STAFF_RW;
            }
            ],
        ];

        return array_merge($rules, $this->rulesUsername(), $this->rulesEmail());
    }

    protected function rulesUsername()
    {
        return [
            ['username', 'trim'],
            ['username', 'string', 'length' => [4, 255]],
            [
                'username',
                'match',
                'pattern' => '/^[a-z0-9_.]{4,255}$/',
                'message' => Yii::t('app', 'error.username.pattern')
            ],
            [
                'username',
                'unique',
                'targetClass' => User::class,
                'message' => Yii::t('app', 'error.username.taken'),
            ],
        ];
    }

    protected function rulesEmail()
    {
        return [
            ['email', 'trim'],
            ['email', 'email'],
            ['email', 'string', 'max' => User::MAX_LENGTH],
            [
                'email',
                'unique',
                'targetClass' => User::class,
                'message' => Yii::t('app', 'error.email.taken'),
            ],
        ];
    }

    public static function generateTemplateFile()
    {
        $path = Yii::getAlias('@webroot/storage') . '/template-users-import.xlsx';

        $writer = WriterEntityFactory::createXLSXWriter();
        $writer->openToFile($path);

        $columnHeaders = [
            'username', 'email', 'password', 'role', 'name',
            'phone', 'address', 'rt', 'rw', 'kabkota', 'kecamatan', 'kelurahan',
        ];

        $writer->addRow(WriterEntityFactory::createRowFromArray($columnHeaders));

        $writer->addRows([
            WriterEntityFactory::createRowFromArray([
                'username_import1', 'email1@gmail.com', 'secret', 'TRAINER', 'User Satu',
                '0812123', 'Jl. Bogor', '01', '01', 'KAB. BOGOR', 'NANGGUNG', 'CISARUA',
            ]),
            WriterEntityFactory::createRowFromArray([
                'username_import2', 'email2@gmail.com', 'secret', 'TRAINER', 'User Dua',
                '0812123', 'Jl. Bogor', '01', '01', 'KAB. BOGOR', 'NANGGUNG', 'CISARUA',
            ]),
        ]);

        $writer->close();

        return $path;
    }

    public function validateKabkota($attribute, $params)
    {
        $areaId = $this->$attribute;
        $area   = Area::findOne(['id' => $areaId]);

        if ($area === null) {
            $this->addError($attribute, 'Kabupaten/Kota tidak ditemukan.');

            return false;
        }
    }

    public function validateKecamatan($attribute, $params)
    {
        $areaId = $this->$attribute;
        $area   = Area::findOne(['id' => $areaId, 'parent_id' => $this->kabkota_id]);

        if ($area === null) {
            $this->addError($attribute, 'Kecamatan tidak ditemukan.');

            return false;
        }
    }

    public function validateKelurahan($attribute, $params)
    {
        $areaId = $this->$attribute;
        $area   = Area::findOne(['id' => $areaId, 'parent_id' => $this->kec_id]);

        if ($area === null) {
            $this->addError($attribute, 'Kelurahan tidak ditemukan.');

            return false;
        }
    }
}
