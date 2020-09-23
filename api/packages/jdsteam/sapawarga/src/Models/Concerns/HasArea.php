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

    protected function getKabkotaField()
    {
        if (empty($this->kabkota)) {
            return null;
        }

        return [
            'id' => $this->kabkota->id,
            'code_bps' => $this->kabkota->code_bps,
            'name' => $this->kabkota->name,
        ];
    }

    protected function getKecamatanField()
    {
        if (empty($this->kecamatan)) {
            return null;
        }

        return [
            'id' => $this->kecamatan->id,
            'code_bps' => $this->kecamatan->code_bps,
            'name' => $this->kecamatan->name,
        ];
    }

    protected function getKelurahanField()
    {
        if (empty($this->kelurahan)) {
            return null;
        }

        return [
            'id' => $this->kelurahan->id,
            'code_bps' => $this->kelurahan->code_bps,
            'name' => $this->kelurahan->name,
        ];
    }

    protected function getKabkotaBpsField()
    {
        return [
            'code_bps' => $this->domicile_kabkota_bps_id,
            'name' => $this->domicile_kabkota_name,
        ];
    }

    protected function getKecBpsField()
    {
        return [
            'code_bps' => $this->domicile_kec_bps_id,
            'name' => $this->domicile_kec_name,
        ];
    }

    protected function getKelBpsField()
    {
        return [
            'code_bps' => $this->domicile_kel_bps_id,
            'name' => $this->domicile_kel_name,
        ];
    }

    protected function getProvBpsField()
    {
        return [
            'code_bps' => $this->domicile_province_bps_id,
            'name' => 'JAWA BARAT',
        ];
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
}
