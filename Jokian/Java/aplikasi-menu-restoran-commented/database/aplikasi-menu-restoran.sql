-- phpMyAdmin SQL Dump                                               -- File ini dihasilkan otomatis oleh phpMyAdmin, alat manajemen database berbasis web
-- version 5.2.3-1.fc43                                             -- Versi phpMyAdmin yang digunakan saat membuat dump ini
-- https://www.phpmyadmin.net/                                      -- Alamat resmi website phpMyAdmin
--
-- Host: localhost                                                   -- Database dijalankan di server lokal (komputer sendiri)
-- Generation Time: Mar 28, 2026 at 05:16 AM                        -- Waktu saat file SQL ini dibuat/diekspor
-- Server version: 8.4.8                                            -- Versi MySQL Server yang digunakan
-- PHP Version: 8.4.17                                              -- Versi PHP yang digunakan oleh phpMyAdmin

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";                             -- Mengatur mode SQL agar AUTO_INCREMENT tidak dimulai dari 0
START TRANSACTION;                                                  -- Memulai blok transaksi; perubahan baru tersimpan permanen saat COMMIT dijalankan
SET time_zone = "+00:00";                                           -- Mengatur zona waktu sesi database ke UTC+00:00 agar konsisten


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;   -- Menyimpan pengaturan karakter set klien yang lama agar bisa dipulihkan setelah import
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */; -- Menyimpan pengaturan karakter set hasil query yang lama agar bisa dipulihkan setelah import
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;   -- Menyimpan pengaturan collation koneksi yang lama agar bisa dipulihkan nanti
/*!40101 SET NAMES utf8mb4 */;                                      -- Mengatur karakter set koneksi ke utf8mb4 agar mendukung semua karakter Unicode termasuk emoji

--
-- Database: `aplikasi-menu-restoran`                               -- Nama database yang menjadi target dari semua perintah SQL di bawah ini
--

-- --------------------------------------------------------

--
-- Table structure for table `menu`                                  -- Blok berikutnya berisi struktur (definisi kolom) dari tabel menu
--

CREATE TABLE `menu` (                                               -- Membuat tabel baru bernama "menu" dengan definisi kolom-kolomnya
  `id` int NOT NULL,                                                -- Kolom "id" bertipe bilangan bulat, bersifat NOT NULL (tidak boleh kosong)
  `nama_menu` varchar(100) NOT NULL,                                -- Kolom "nama_menu" bertipe teks maks 100 karakter, tidak boleh kosong
  `harga` int NOT NULL                                              -- Kolom "harga" bertipe bilangan bulat, tidak boleh kosong, menyimpan harga dalam rupiah
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci; -- Mesin InnoDB (mendukung transaksi), charset utf8mb4 untuk dukungan Unicode penuh

--
-- Indexes for dumped tables                                         -- Blok berikutnya berisi perintah pembuatan indeks untuk tabel-tabel di atas
--

--
-- Indexes for table `menu`                                          -- Indeks yang dibuat di bawah ini adalah milik tabel menu
--
ALTER TABLE `menu`                                                  -- Mengubah struktur tabel "menu" untuk menambahkan indeks
  ADD PRIMARY KEY (`id`);                                           -- Menjadikan kolom "id" sebagai PRIMARY KEY (nilai unik, pengenal utama setiap baris)

--
-- AUTO_INCREMENT for dumped tables                                  -- Blok berikutnya mengatur nilai AUTO_INCREMENT untuk tabel-tabel terkait
--

--
-- AUTO_INCREMENT for table `menu`                                   -- Pengaturan AUTO_INCREMENT berikut berlaku untuk tabel menu
--
ALTER TABLE `menu`                                                  -- Mengubah struktur tabel "menu" untuk mengaktifkan AUTO_INCREMENT pada kolom id
  MODIFY `id` int NOT NULL AUTO_INCREMENT;                          -- Kolom "id" kini otomatis bertambah setiap ada data baru, tidak perlu diisi manual
COMMIT;                                                             -- Menyimpan semua perubahan dalam transaksi ini secara permanen ke database

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;     -- Memulihkan kembali pengaturan karakter set klien ke nilai semula sebelum import
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;   -- Memulihkan kembali pengaturan karakter set hasil query ke nilai semula sebelum import
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;     -- Memulihkan kembali pengaturan collation koneksi ke nilai semula sebelum import
