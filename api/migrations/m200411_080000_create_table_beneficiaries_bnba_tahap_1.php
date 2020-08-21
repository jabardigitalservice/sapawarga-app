<?php

use app\components\CustomMigration;

/**
 * Class m200411_065139_create_table_beneficiaries */
class m200411_080000_create_table_beneficiaries_bnba_tahap_1 extends CustomMigration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $query=<<<SQL
            CREATE TABLE `beneficiaries_bnba_tahap_1` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `is_dtks` tinyint(1) DEFAULT NULL,
              `id_tipe_bansos` int(11) DEFAULT NULL,
              `nama_kab` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `nama_kec` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `nama_kel` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `kode_kab` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `kode_kec` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `kode_kel` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `rw` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `rt` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `nik` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `nama_krt` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `no_kk` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `alamat` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `jumlah_art_tanggungan` int(11) DEFAULT NULL,
              `nomor_hp` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `lapangan_usaha` int(11) DEFAULT NULL,
              `status_kedudukan` int(11) DEFAULT NULL,
              `penghasilan_sebelum_covid19` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `penghasilan_setelah_covid` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `keterangan` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `id_manual` int(11) DEFAULT NULL,
              `id_sapawarga` int(11) DEFAULT NULL,
              `id_pikobar` int(11) DEFAULT NULL,
              `created_time` timestamp NULL DEFAULT NULL,
              `updated_time` timestamp NULL DEFAULT NULL,
              `deleted_time` timestamp NULL DEFAULT NULL,
              `is_deleted` tinyint(1) DEFAULT NULL,
              `received_date` timestamp NULL DEFAULT NULL,
              `is_nik_valid` tinyint(1) DEFAULT NULL,
              `is_alamat_lengkap` tinyint(1) DEFAULT NULL,
              `is_manual` tinyint(1) DEFAULT NULL,
              `is_sapawarga` tinyint(1) DEFAULT NULL,
              `is_pikobar` tinyint(1) DEFAULT NULL,
              `is_super_clean` tinyint(1) DEFAULT NULL,
              `is_data_sisa` tinyint(1) DEFAULT NULL,
              `tahap_bantuan` int(11) DEFAULT NULL,
              `notes` text COLLATE utf8mb4_unicode_ci,
              `id_dtks` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `id_art_dtks` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `id_keluarga` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
              `is_address_checked` tinyint(1) DEFAULT '0',
              `is_bansos_kemensos` tinyint(1) DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `bnba_nik_idx` (`nik`,`nama_krt`,`kode_kab`,`kode_kec`,`kode_kel`),
              KEY `bnba_tipe_idx` (`id_tipe_bansos`,`is_dtks`,`is_deleted`,`kode_kab`,`kode_kec`,`kode_kel`,`rw`) USING BTREE,
              KEY `bnba_area_idx` (`kode_kab`,`kode_kec`,`kode_kel`,`rw`,`is_deleted`) USING BTREE,
              KEY `idx-bnba-type-tahap` (`is_deleted`,`tahap_bantuan`,`id_tipe_bansos`,`is_dtks`,`kode_kab`,`kode_kec`,`kode_kel`,`rw`,`rt`) USING BTREE,
              KEY `idx-bnba-type-tahap-prov` (`is_deleted`,`tahap_bantuan`,`id_tipe_bansos`,`is_dtks`,`kode_kab`) USING BTREE,
              KEY `idx-bnba-kode-kel` (`kode_kel`) USING BTREE,
              KEY `idx-bnba-kode-kec` (`kode_kec`,`kode_kel`) USING BTREE,
            ) ENGINE=InnoDB AUTO_INCREMENT=55005 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
SQL;
        \Yii::$app->db->createCommand($query)->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('beneficiaries_bnba_tahap_1');
    }
}
