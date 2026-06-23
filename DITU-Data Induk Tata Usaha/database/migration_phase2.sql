-- ============================================================
-- Migration: Phase 2 - Database Schema Improvements
-- Run this after the initial database.sql
-- ============================================================

USE sik_sdn001gs;

-- 1. Add unique constraint to prevent duplicate siswa (nama + rombel)
-- Note: We use ALTER TABLE to add a composite index (not strict unique, 
-- because some schools have same-name students in different rombels)
ALTER TABLE `DataSiswa` ADD INDEX `idx_nama_rombel` (`nama`, `rombel`);

-- 2. Add semester/year columns to JadwalPelajaran for filtering
ALTER TABLE `JadwalPelajaran` ADD COLUMN `semester` VARCHAR(10) DEFAULT '' AFTER `keterangan`;
ALTER TABLE `JadwalPelajaran` ADD COLUMN `tahun_ajaran` VARCHAR(20) DEFAULT '' AFTER `semester`;

-- 3. Add Nilai table improvements - add siswa_id FK column for future integrity
ALTER TABLE `Nilai` ADD COLUMN `siswa_id` VARCHAR(50) DEFAULT NULL AFTER `id`;
ALTER TABLE `Nilai` ADD INDEX `idx_siswa_id` (`siswa_id`);

-- 4. Add sort_order to BankSoal for question ordering
ALTER TABLE `BankSoal` ADD COLUMN `sort_order` INT DEFAULT 0 AFTER `gambar`;

-- 5. Add status column to PaketSoal for draft/published states
ALTER TABLE `PaketSoal` ADD COLUMN `status` VARCHAR(20) DEFAULT 'draft' AFTER `dibuat_at`;

-- 6. Improve Settings table with category support
ALTER TABLE `Settings` ADD COLUMN `category` VARCHAR(50) DEFAULT 'general' AFTER `setting_value`;

-- 7. Add timestamps to DataSiswa and DataPTK
ALTER TABLE `DataSiswa` ADD COLUMN `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP AFTER `foto`;
ALTER TABLE `DataSiswa` ADD COLUMN `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

ALTER TABLE `DataPTK` ADD COLUMN `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP AFTER `keterangan`;
ALTER TABLE `DataPTK` ADD COLUMN `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

-- 8. Add proper indexes for common query patterns
ALTER TABLE `Nilai` ADD INDEX `idx_rombel_mapel` (`rombel`, `mapel`);
ALTER TABLE `Nilai` ADD INDEX `idx_semester_tahun` (`semester`, `tahun_ajaran`);
ALTER TABLE `BankSoal` ADD INDEX `idx_mapel_kelas` (`mapel`, `kelas`);
ALTER TABLE `BankSoal` ADD INDEX `idx_tipe_semester` (`tipe`, `semester`);
ALTER TABLE `JadwalPelajaran` ADD INDEX `idx_hari_rombel` (`hari`, `rombel`);
ALTER TABLE `ActivityLog` ADD INDEX `idx_user_action` (`user_id`, `action`);
ALTER TABLE `ActivityLog` ADD INDEX `idx_created_at` (`created_at`);
