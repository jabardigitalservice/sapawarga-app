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

    // Get name by code_bps NIK
    public function getKelurahanNikBps()
    {
        return $this->hasOne(Area::className(), ['code_bps' => 'kel_bps_id']);
    }

    public function getKecamatanNikBps()
    {
        return $this->hasOne(Area::className(), ['code_bps' => 'kec_bps_id']);
    }

    public function getKabkotaNikBps()
    {
        return $this->hasOne(Area::className(), ['code_bps' => 'kabkota_bps_id']);
    }

    public function getProvinceNikBps()
    {
        return $this->hasOne(Area::className(), ['code_bps' => 'province_bps_id']);
    }

    protected function getKabkotaBpsField()
    {
        if (empty($this->kabkotaNikBps)) {
            return null;
        }

        return [
            'id' => $this->kabkotaNikBps->id,
            'code_bps' => $this->kabkotaNikBps->code_bps,
            'name' => $this->kabkotaNikBps->name,
        ];
    }

    protected function getKecBpsField()
    {
        if (empty($this->kecamatanNikBps)) {
            return null;
        }

        return [
            'id' => $this->kecamatanNikBps->id,
            'code_bps' => $this->kecamatanNikBps->code_bps,
            'name' => $this->kecamatanNikBps->name,
        ];
    }

    protected function getKelBpsField()
    {
        if (empty($this->kelurahanNikBps)) {
            return null;
        }

        return [
            'id' => $this->kelurahanNikBps->id,
            'code_bps' => $this->kelurahanNikBps->code_bps,
            'name' => $this->kelurahanNikBps->name,
        ];
    }

    protected function getProvBpsField()
    {
        if (empty($this->provinceNikBps)) {
            return null;
        }

        return [
            'id' => $this->provinceNikBps->id,
            'code_bps' => $this->provinceNikBps->code_bps,
            'name' => $this->provinceNikBps->name,
        ];
    }


    // Get name by code_bps domicile
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
            'code_bps' => $this->kabkotaBps->code_bps,
            'name' => $this->kabkotaBps->name,
        ];
    }

    protected function getDomicileKecField()
    {
        if (empty($this->kecamatanBps)) {
            return null;
        }

        return [
            'code_bps' => $this->kecamatanBps->code_bps,
            'name' => $this->kecamatanBps->name,
        ];
    }

    protected function getDomicileKelField()
    {
        if (empty($this->kelurahanBps)) {
            return null;
        }

        return [
            'code_bps' => $this->kelurahanBps->code_bps,
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
