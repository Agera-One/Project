<?php
include '../konfigurasi/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: register.php");
    exit;
}

$nama       = trim($_POST['nama'] ?? '');
$nik        = trim($_POST['nik'] ?? '');
$tgl_lahir  = $_POST['tgl_lahir'] ?? '';
$no_hp      = trim($_POST['no_hp'] ?? '');
$email      = trim($_POST['email'] ?? '');
$alamat     = trim($_POST['alamat'] ?? '');
$password   = $_POST['password'] ?? '';
$confirm    = $_POST['confirm_password'] ?? '';

/* ================= VALIDASI ================= */

// data kosong
if (!$nama || !$nik || !$email || !$password || !$confirm) {
    header("Location: ../auth/register.php?error=kosong");
    exit;
}

// password beda
if ($password !== $confirm) {
    header("Location: ../auth/register.php?error=password");
    exit;
}

// password pendek
if (strlen($password) < 6) {
    header("Location: ../auth/register.php?error=pendek");
    exit;
}

// admin tidak boleh daftar
if ($email === 'adminklinik@gmail.com') {
    header("Location: ../auth/register.php?error=admin");
    exit;
}

// email sudah ada
$cek = mysqli_query($conn, "SELECT id FROM users WHERE email='$email'");
if (mysqli_num_rows($cek) > 0) {
    header("Location: ../auth/register.php?error=terdaftar");
    exit;
}

/* ================= SIMPAN DATA ================= */

$hashed = password_hash($password, PASSWORD_DEFAULT);

// insert users
$simpanUser = mysqli_query($conn, "
    INSERT INTO users (email, password, role)
    VALUES ('$email','$hashed','pasien')
");

if (!$simpanUser) {
    header("Location: ../auth/register.php?error=gagal");
    exit;
}

$user_id = mysqli_insert_id($conn);

// ============================
// GENERATE NO RM OTOMATIS
// FORMAT: RM-2026-0001
// ============================
$tahun = date('Y');

// ambil no_rm terakhir tahun ini
$qRM = mysqli_query($conn, "
    SELECT no_rm 
    FROM pasien 
    WHERE no_rm LIKE 'RM-$tahun-%'
    ORDER BY id DESC 
    LIMIT 1
");

$urut = 1;

if ($row = mysqli_fetch_assoc($qRM)) {
    $last = (int) substr($row['no_rm'], -4);
    $urut = $last + 1;
}

$no_rm = 'RM-' . $tahun . '-' . str_pad($urut, 4, '0', STR_PAD_LEFT);


// insert pasien
$simpanPasien = mysqli_query($conn, "
    INSERT INTO pasien (user_id, no_rm, nik, nama, tgl_lahir, no_hp, email, alamat)
    VALUES ('$user_id','$no_rm','$nik','$nama','$tgl_lahir','$no_hp','$email','$alamat')
");

if (!$simpanPasien) {
    header("Location: ../auth/register.php?error=gagal");
    exit;
}

// sukses
header("Location: ../auth/login.php?register=success");
exit;
