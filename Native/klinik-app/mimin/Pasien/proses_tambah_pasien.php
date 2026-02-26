<?php
include '../../konfigurasi/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../Pasien/tambah_pasien.php");
    exit;
}

/* ================= AMBIL DATA ================= */

$nama      = trim($_POST['nama'] ?? '');
$nik       = trim($_POST['nik'] ?? '');
$tgl_lahir = $_POST['tgl_lahir'] ?? '';
$no_hp     = trim($_POST['no_hp'] ?? '');
$email     = trim($_POST['email'] ?? '');
$alamat    = trim($_POST['alamat'] ?? '');

/* ================= VALIDASI ================= */

if (!$nama || !$nik || !$tgl_lahir || !$no_hp || !$alamat) {
    die('DATA WAJIB BELUM LENGKAP');
}

// cek NIK
$cek = mysqli_query($conn, "SELECT id FROM pasien WHERE nik='$nik'");
if (!$cek) {
    die('QUERY CEK NIK GAGAL');
}

if (mysqli_num_rows($cek) > 0) {
    header("Location: ../Pasien/tambah_pasien.php?error=nik");
    exit;
}

/* ================= GENERATE NO RM ================= */

$tahun = date('Y');

$q = mysqli_query($conn, "
    SELECT no_rm FROM pasien
    WHERE no_rm LIKE 'RM-$tahun-%'
    ORDER BY no_rm DESC
    LIMIT 1
");

if (!$q) {
    die('QUERY AMBIL RM TERAKHIR GAGAL');
}

if (mysqli_num_rows($q) > 0) {
    $d = mysqli_fetch_assoc($q);
    $last = (int) substr($d['no_rm'], -4);
    $urut = $last + 1;
} else {
    $urut = 1;
}

$no_rm = 'RM-' . $tahun . '-' . str_pad($urut, 4, '0', STR_PAD_LEFT);

/* ================= SIMPAN ================= */

$simpan = mysqli_query($conn, "
    INSERT INTO pasien 
    (no_rm, nik, nama, tgl_lahir, no_hp, email, alamat)
    VALUES 
    ('$no_rm','$nik','$nama','$tgl_lahir','$no_hp','$email','$alamat')
");

if (!$simpan) {
    die('GAGAL SIMPAN PASIEN: ' . mysqli_error($conn));
}

/* ================= REDIRECT ================= */

header("Location: http://localhost/klinik_app/mimin/RegisterPasien/register_pasien.php?success=1");
exit;
?>