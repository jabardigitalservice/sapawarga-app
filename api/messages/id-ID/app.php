<?php

/**
* Translation map for id-ID, app category
*/
return [
    'app.username' => 'Username',
    'app.email' => 'Email',
    'app.password' => 'Kata Sandi',
    'app.role' => 'Role',
    'app.name' => 'Nama Lengkap',
    'app.phone' => 'Nomor Telepon',
    'app.address' => 'Alamat',
    'app.rt' => 'RT',
    'app.rw' => 'RW',
    'app.kel_id' => 'Desa/Kelurahan',
    'app.kec_id' => 'Kecamatan',
    'app.kabkota_id' => 'Kabupaten/Kota',
    'app.job_type_id' => 'Pekerjaan',
    'app.education_level_id' => 'Pendidikan',

    'error.login.incorrect' => 'Username atau kata sandi salah.',
    'error.login.inactive' => 'Username belum aktif, Anda dapat menghubungi nomor 082315192724 (SMS dan WhatsApp) dan email sapawarga@jabarprov.go.id untuk mengaktifkan akun Anda.',
    'error.email.taken' => 'Alamat email sudah digunakan.',
    'error.username.taken' => 'Username sudah digunakan.',
    'error.username.pattern' => 'Username hanya boleh menggunakan karakter alfanumerik huruf kecil, underscore, dan titik.',
    'error.category.taken' => 'Nama kategori sudah digunakan.',
    'error.category.default.required' => 'Tipe kategori ini harus mempunyai nama default \'Lainnya\'',
    'error.role.permission' => 'Anda tidak diperbolehkan untuk melakukan aksi ini.',
    'error.rw.pattern' => 'RW harus terdiri dari 3 karakter dan hanya boleh menggunakan karakter numerik (0-9).',
    'error.id.invalid' => 'id tidak valid.',
    'error.approvalnote.exist' => 'Keterangan tidak boleh diisi',
    'error.password.old.empty' => 'Masukan kata sandi lama anda.',
    'error.password.old.incorrect' => 'Kata sandi lama anda salah.',
    'error.password.confirmation.incorect' => 'Kata sandi konfirmasi anda salah.',
    'error.password.old.same' => 'Kata sandi baru tidak boleh sama dengan kata sandi lama.',
    'error.empty.internalfill' => 'Silahkan mengisi internal category',
    'error.empty.externalfill' => 'Silahkan mengisi link url',
    'error.validation.rangedatefill' => 'Rentang waktu tersebut telah digunakan',
    'error.validation.enddate_less_than_today' => 'Tanggal berakhir tidak boleh kurang dari hari ini',

    'error.nik.invalid' => 'Format NIK tidak valid.',
    'error.nik.notfound' => 'NIK tidak terdaftar di Disdukcapil.',
    'error.nik.taken' => 'NIK sudah terdaftar.',
    'error.nik.limit' => 'Tunggu beberapa saat untuk bisa Cek NIK kembali.',

    'error.scheduled_datetime.must_after_now' => 'Jadwal yang dipilih telah lewat.',

    'error.image.invalid_format' => 'File "{file}" bukan berupa gambar',
    'error.image.should_exact' => 'Gambar "{file}" harus berukuran {width, number}x{height, number} pixel.',

    'role.service_account' => 'Service Account',
    'role.admin' => 'Administrator',
    'role.pimpinan' => 'Pimpinan',
    'role.staffProv' => 'Staf Provinsi',
    'role.staffSaberhoax' => 'Staf Saber Hoaks',
    'role.staffOPD' => 'Staf OPD',
    'role.staffKabkota' => 'Staf Kabupaten/Kota',
    'role.staffKec' => 'Staf Kecamatan',
    'role.staffKel' => 'Staf Desa/Kelurahan',
    'role.staffRW' => 'RW',
    'role.trainer' => 'Pelatih',
    'role.user' => 'Pengguna',

    'status.active' => 'Aktif',
    'status.inactive' => 'Tidak Aktif',
    'status.deleted' => 'Dihapus',
    'status.draft' => 'Draft',
    'status.canceled' => 'Dibatalkan',
    'status.scheduled' => 'Dijadwalkan',
    'status.sent' => 'Terkirim',
    'status.published' => 'Dipublikasikan',
    'status.unpublished' => 'Tidak Dipublikasikan',
    'status.approval-pending'  => 'Terkirim',
    'status.approval-rejected' => 'Ditolak',

    'status.beneficiary.pending' => 'Belum Terverifikasi',
    'status.beneficiary.reject' => 'Ditolak',
    'status.beneficiary.approved' => 'Terverifikasi',

    'type.beneficiaries.pkh' => 'PKH',
    'type.beneficiaries.bnpt' => 'BNPT',
    'type.beneficiaries.bnpt_perluasan' => 'BNPT PERLUASAN',
    'type.beneficiaries.bansos_tunai' => 'BANSOS TUNAI',
    'type.beneficiaries.bansos_presiden_sembako' => 'BANSOS PRESIDEN SEMBAKO',
    'type.beneficiaries.bansos_provinsi' => 'BANSOS PROVINSI',
    'type.beneficiaries.dana_desa' => 'DANA DESA',
    'type.beneficiaries.bansos_kabkota' => 'BANSOS KABUPATEN KOTA',
];
