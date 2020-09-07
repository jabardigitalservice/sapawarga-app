<?php

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\Query;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "beneficiaries_bnba_monitoring_uploads".
 *
 * @property id $id
 * @property integer $code_bps
 * @property string $kabkota_name
 * @property integer $tahap_bantuan
 * @property integer $is_dtks
 * @property integer $last_updated
 * @property integer $created_at
 * @property integer $updated_at
 */

class BeneficiaryBnbaMonitoringUpload extends ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'beneficiaries_bnba_monitoring_uploads';
    }

    public function rules()
    {
        return [
            [
                ['code_bps', 'kabkota_name', 'tahap_bantuan', 'is_dtks', 'last_updated'],
                'trim'
            ],
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
            ]
        ];
    }

    static function updateData()
    {
        $tahapBantuan = null;
        $data = (new \yii\db\Query())
            ->from('beneficiaries_current_tahap')
            ->all();

        if (count($data)) {
            $tahapBantuan = $data[0]['current_tahap_bnba'];
        }

        $rawQuery = <<<SQL
            SELECT
              areas.name,
              kode_kab as code_bps,
              is_dtks_final as type,
              last_updated as last_update
            FROM
              (SELECT
                  kode_kab,
                  MAX(updated_time) as last_updated,
                  CASE is_dtks
                      WHEN 1 THEN 'dtks'
                      ELSE 'non-dtks' # null dan nilai lainnya
                  END is_dtks_final
              FROM beneficiaries_bnba_tahap_1
              WHERE
                (is_deleted <> 1 OR is_deleted IS NULL)
                AND tahap_bantuan = :tahap_bantuan
              GROUP BY is_dtks_final, kode_kab
              ) as monitoring_list
            LEFT JOIN areas ON areas.code_bps = kode_kab
            ;
SQL;
        $query = Yii::$app->db
            ->createCommand($rawQuery, [':tahap_bantuan' => $tahapBantuan]);

        $rows = $query->queryAll();

        // store to cache
        foreach ($rows as $row) {
            self::updateAll(
                //set
                ['last_updated' => strtotime($row['last_update']) ],
                //where
                [
                    'code_bps' => $row['code_bps'],
                    'is_dtks' => ($row['type'] == 'dtks') ? 1 : 0,
                ]
            );
        }
    }

}
