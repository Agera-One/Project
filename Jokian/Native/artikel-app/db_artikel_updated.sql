-- ============================================================
-- db_artikel_updated.sql
-- Database dengan kolom tambahan untuk mendukung bcrypt upgrade
-- ============================================================

-- Jalankan file db_artikel.sql terlebih dahulu, lalu jalankan ini.
-- ATAU gunakan file ini sebagai pengganti lengkap.

-- Tambahkan kolom hash_type untuk tracking jenis hash password
-- (opsional, berguna untuk audit)
ALTER TABLE `users`
  ADD COLUMN `hash_type` ENUM('md5', 'bcrypt') DEFAULT 'md5'
  AFTER `password`;

-- Update existing users sebagai md5 (sesuai data awal)
UPDATE `users` SET `hash_type` = 'md5';

-- Setelah user login dan hash di-upgrade, kolom ini akan otomatis diperbarui
-- Tambahkan trigger untuk update hash_type saat password berubah ke bcrypt
-- (Alternatif: lakukan di PHP saja — sudah dihandle di User::upgradePasswordToBcrypt())

-- Index untuk performa query artikel
CREATE INDEX IF NOT EXISTS idx_articles_created_at ON articles(created_at DESC);
CREATE INDEX IF NOT EXISTS idx_articles_author_id ON articles(author_id);
