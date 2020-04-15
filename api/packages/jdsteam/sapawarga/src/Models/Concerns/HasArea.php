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


    // Get name by code_bps

    public function getKelurahanBps()
    {
        return $this->hasOne(Area::className(), ['code_bps' => 'domicile_kel_bps_id']);
    }

    public function getKecamatanBps()
    {
        return $this->hasOne(Area::className(), ['code_bps' => 'domicile_kec_bps_id']);
    }

    public function getKabkotaBps()
    {
        return $this->hasOne(Area::className(), ['code_bps' => 'domicile_kabkota_bps_id']);
    }


    protected function getDomicileKabkotaField()
    {
        if (empty($this->kabkotaBps)) {
            return null;
        }

        return [
            'code_bps'   => $this->kabkotaBps->code_bps,
            'name' => $this->kabkotaBps->name,
        ];
    }

    protected function getDomicileKecField()
    {
        if (empty($this->kecamatanBps)) {
            return null;
        }

        return [
            'code_bps'   => $this->kecamatanBps->code_bps,
            'name' => $this->kecamatanBps->name,
        ];
    }

    protected function getDomicileKelField()
    {
        if (empty($this->kelurahanBps)) {
            return null;
        }

        return [
            'code_bps'   => $this->kelurahanBps->code_bps,
            'name' => $this->kelurahanBps->name,
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
