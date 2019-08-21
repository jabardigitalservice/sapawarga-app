<?php

namespace Jdsteam\Sapawarga\Models\Concerns;

use app\models\Area;
use Yii;

trait HasArea
{
    public function getKelurahan()
    {
        return $this->hasOne(Area::className(), ['id' => 'kel_id']);
    }

    public function getKecamatan()
    {
        return $this->hasOne(Area::className(), ['id' => 'kec_id']);
    }

    public function getKabkota()
    {
        return $this->hasOne(Area::className(), ['id' => 'kabkota_id']);
    }

    protected function rulesRw()
    {
        return [
            [
                'rw',
                'match',
                'pattern' => '/^[0-9]{3}$/',
                'message' => Yii::t('app', 'error.rw.pattern'),
            ],
            ['rw', 'default'],
        ];
    }

    protected function getKabkotaField()
    {
        if (empty($this->kabkota)) {
            return null;
        }

        return [
            'id'   => $this->kabkota->id,
            'name' => $this->kabkota->name,
        ];
    }

    protected function getKecamatanField()
    {
        if (empty($this->kecamatan)) {
            return null;
        }

        return [
            'id'   => $this->kecamatan->id,
            'name' => $this->kecamatan->name,
        ];
    }

    protected function getKelurahanField()
    {
        if (empty($this->kelurahan)) {
            return null;
        }

        return [
            'id'   => $this->kelurahan->id,
            'name' => $this->kelurahan->name,
        ];
    }
}
