CREATE DATABASE IF NOT EXISTS db_ditu;
USE db_ditu;

CREATE TABLE IF NOT EXISTS `DataSiswa` (
  `id` VARCHAR(50) PRIMARY KEY,
  `nipd` VARCHAR(50),
  `nisn` VARCHAR(50),
  `nik` VARCHAR(50),
  `nama` VARCHAR(150),
  `jk` VARCHAR(10),
  `tempat_lahir` VARCHAR(100),
  `tgl_lahir` DATE,
  `agama` VARCHAR(50),
  `anak_ke` INT,
  `jml_saudara` INT,
  `hp_siswa` VARCHAR(50),
  `alamat` TEXT,
  `rt` VARCHAR(10),
  `rw` VARCHAR(10),
  `kelurahan` VARCHAR(100),
  `kecamatan` VARCHAR(100),
  `kabupaten` VARCHAR(100),
  `provinsi` VARCHAR(100),
  `kode_pos` VARCHAR(10),
  `jarak_sekolah` FLOAT,
  `transportasi` VARCHAR(50),
  `nama_ayah` VARCHAR(100),
  `nik_ayah` VARCHAR(50),
  `pendidikan_ayah` VARCHAR(50),
  `pekerjaan_ayah` VARCHAR(100),
  `penghasilan_ayah` VARCHAR(100),
  `hp_ayah` VARCHAR(50),
  `nama_ibu` VARCHAR(100),
  `nik_ibu` VARCHAR(50),
  `pendidikan_ibu` VARCHAR(50),
  `pekerjaan_ibu` VARCHAR(100),
  `penghasilan_ibu` VARCHAR(100),
  `hp_ibu` VARCHAR(50),
  `nama_wali` VARCHAR(100),
  `hubungan_wali` VARCHAR(50),
  `hp_wali` VARCHAR(50),
  `rombel` VARCHAR(50),
  `tahun_masuk` VARCHAR(10),
  `status_siswa` VARCHAR(50),
  `asal_sekolah` VARCHAR(100),
  `kip` VARCHAR(50),
  `no_kip` VARCHAR(50),
  `kebutuhan_khusus` VARCHAR(100),
  `tinggi_badan` FLOAT,
  `berat_badan` FLOAT,
  `catatan` TEXT,
  `foto` TEXT
);

CREATE TABLE IF NOT EXISTS `DataPTK` (
  `id` VARCHAR(50) PRIMARY KEY,
  `user_id` VARCHAR(50),
  `nama` VARCHAR(150),
  `jk` VARCHAR(10),
  `nik` VARCHAR(50),
  `tempat_lahir` VARCHAR(100),
  `tgl_lahir` DATE,
  `agama` VARCHAR(50),
  `status_nikah` VARCHAR(50),
  `jml_anak` INT,
  `nip` VARCHAR(50),
  `nuptk` VARCHAR(50),
  `nrg` VARCHAR(50),
  `jenis_ptk` VARCHAR(100),
  `status_kepeg` VARCHAR(50),
  `status_aktif` VARCHAR(50),
  `tmt_pengangkatan` DATE,
  `tmt_sekolah` DATE,
  `golongan` VARCHAR(50),
  `sertifikasi` VARCHAR(50),
  `tahun_sertif` VARCHAR(10),
  `gaji_pokok` INT,
  `pendidikan` VARCHAR(50),
  `jurusan` VARCHAR(100),
  `perguruan_tinggi` VARCHAR(150),
  `tahun_lulus` VARCHAR(10),
  `no_ijazah` VARCHAR(100),
  `alamat` TEXT,
  `rt_rw` VARCHAR(20),
  `kelurahan` VARCHAR(100),
  `kecamatan` VARCHAR(100),
  `kabupaten` VARCHAR(100),
  `provinsi` VARCHAR(100),
  `kode_pos` VARCHAR(10),
  `hp` VARCHAR(50),
  `email` VARCHAR(100),
  `no_rekening` VARCHAR(50),
  `nama_bank` VARCHAR(100),
  `npwp` VARCHAR(50),
  `bpjs` VARCHAR(50),
  `mapel_diampu` VARCHAR(150),
  `rombel_diampu` VARCHAR(100),
  `jam_mengajar` INT,
  `tugas_tambahan` VARCHAR(150),
  `username_sistem` VARCHAR(50),
  `catatan` TEXT,
  `foto` TEXT,
  `dokumen_ijazah_sd` TEXT,
  `dokumen_ijazah_smp` TEXT,
  `dokumen_ijazah_sma` TEXT,
  `dokumen_ijazah_s1` TEXT,
  `dokumen_kk` TEXT,
  `dokumen_ktp` TEXT,
  `dokumen_npwp` TEXT,
  `dokumen_sertif_1` TEXT,
  `dokumen_sertif_2` TEXT,
  `dokumen_sertif_3` TEXT,
  `dokumen_sertif_4` TEXT,
  `keterangan` VARCHAR(50)
);

CREATE TABLE IF NOT EXISTS `Users` (
  `id` VARCHAR(50) PRIMARY KEY,
  `username` VARCHAR(50) UNIQUE,
  `password` VARCHAR(255),
  `nama` VARCHAR(150),
  `role` VARCHAR(20),
  `email` VARCHAR(100),
  `foto` TEXT,
  `nip` VARCHAR(50),
  `rombel` VARCHAR(50),
  `aktif` VARCHAR(1) DEFAULT '1',
  `last_login` DATETIME
);

INSERT INTO `Users` (`id`, `username`, `password`, `nama`, `role`, `email`, `foto`, `nip`, `rombel`, `aktif`) VALUES
('1', 'admin', '$2y$10$BBIlwOVXSN2v8H.X1p3TB.fkhBN8p0pdBjpSe6KKg0I2vlPavCboW', 'Administrator', 'admin', 'admin@sekolah.sch.id', '', '', '', '1')
ON DUPLICATE KEY UPDATE `username`='admin';

CREATE TABLE IF NOT EXISTS `BankSoal` (
  `id` VARCHAR(50) PRIMARY KEY,
  `mapel` VARCHAR(100),
  `kelas` VARCHAR(50),
  `tipe` VARCHAR(50),
  `semester` VARCHAR(10),
  `tahun_ajaran` VARCHAR(20),
  `kesulitan` VARCHAR(20),
  `pertanyaan` TEXT,
  `pilihan_a` TEXT,
  `pilihan_b` TEXT,
  `pilihan_c` TEXT,
  `pilihan_d` TEXT,
  `jawaban_benar` VARCHAR(10),
  `kunci_jawaban` TEXT,
  `pembahasan` TEXT,
  `dibuat_oleh` VARCHAR(100),
  `gambar` TEXT
);

CREATE TABLE IF NOT EXISTS `JadwalPelajaran` (
  `id` VARCHAR(50) PRIMARY KEY,
  `hari` VARCHAR(20),
  `jam_mulai` VARCHAR(10),
  `jam_selesai` VARCHAR(10),
  `mapel` VARCHAR(100),
  `rombel` VARCHAR(50),
  `guru` VARCHAR(150),
  `ruang` VARCHAR(50),
  `keterangan` TEXT
);

CREATE TABLE IF NOT EXISTS `Nilai` (
  `id` VARCHAR(50) PRIMARY KEY,
  `nama_siswa` VARCHAR(150),
  `rombel` VARCHAR(50),
  `mapel` VARCHAR(100),
  `jenis` VARCHAR(50),
  `semester` VARCHAR(10) DEFAULT '',
  `nilai` FLOAT,
  `tahun_ajaran` VARCHAR(20),
  `keterangan` TEXT
);

CREATE TABLE IF NOT EXISTS `Settings` (
  `key_name` VARCHAR(100) PRIMARY KEY,
  `setting_value` TEXT
);

CREATE TABLE IF NOT EXISTS `Rombel` (
  `id` VARCHAR(50) PRIMARY KEY,
  `nama_rombel` VARCHAR(50)
);

CREATE TABLE IF NOT EXISTS `PaketSoal` (
  `id` VARCHAR(50) PRIMARY KEY,
  `judul` VARCHAR(255),
  `mapel` VARCHAR(100),
  `kelas` VARCHAR(50),
  `semester` VARCHAR(10),
  `tapel` VARCHAR(20),
  `waktu` INT,
  `jumlah_soal` INT,
  `soal_ids` TEXT,
  `dibuat_oleh` VARCHAR(150),
  `dibuat_at` DATETIME
);

CREATE TABLE IF NOT EXISTS `ActivityLog` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` VARCHAR(50),
  `user_name` VARCHAR(150),
  `user_role` VARCHAR(20),
  `action` VARCHAR(50),
  `category` VARCHAR(50),
  `description` TEXT,
  `ip_address` VARCHAR(50),
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS `Absensi` (
  `id` VARCHAR(50) PRIMARY KEY,
  `siswa_id` VARCHAR(50),
  `nama_siswa` VARCHAR(150),
  `rombel` VARCHAR(50),
  `tanggal` DATE,
  `status` VARCHAR(20) DEFAULT 'hadir',
  `keterangan` TEXT,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO `Rombel` (`id`, `nama_rombel`) VALUES
('1', '1A'), ('2', '1B'), ('3', '2A'), ('4', '2B'),
('5', '3A'), ('6', '3B'), ('7', '4A'), ('8', '4B'),
('9', '5A'), ('10', '5B'), ('11', '6A'), ('12', '6B')
ON DUPLICATE KEY UPDATE `nama_rombel`=VALUES(`nama_rombel`);
