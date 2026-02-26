<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

include '../../konfigurasi/koneksi.php';

// Ambil ID dari URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message'] = "ID poli tidak valid!";
    header("Location: ../Poli/poli.php");
    exit;
}

$id = (int)$_GET['id'];

// Cek apakah poli digunakan di jadwal_dokter atau pendaftaran
$digunakan_di_jadwal = mysqli_query($conn, "SELECT id FROM jadwal_dokter WHERE poli_id = $id LIMIT 1");
$digunakan_di_pendaftaran = mysqli_query($conn, "SELECT id FROM pendaftaran WHERE poli_id = $id LIMIT 1");

if (mysqli_num_rows($digunakan_di_jadwal) > 0 || mysqli_num_rows($digunakan_di_pendaftaran) > 0) {
    $_SESSION['error_message'] = "Gagal menghapus! Poliklinik ini masih digunakan oleh dokter atau pasien.";
    header("Location: ../Poli/poli.php");
    exit;
}

// Hapus poli
$hapus = mysqli_query($conn, "DELETE FROM poli WHERE id = $id");

if ($hapus) {
    $_SESSION['success_message'] = "Poliklinik berhasil dihapus!";
} else {
    $_SESSION['error_message'] = "Gagal menghapus poliklinik.";
}

header("Location: ../Poli/poli.php");
exit;
?>