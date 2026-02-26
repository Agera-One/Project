<?php
session_start();
include '../../konfigurasi/koneksi.php';

/* ===== CEK LOGIN ===== */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

/* ===== AMBIL DATA DARI URL ===== */
$step = (int)($_GET['step'] ?? 1);
$pasien = null;
$poli_info = null;
$dokter_info = null;

$pasien_id_from_url = $_GET['pasien_id'] ?? null;
$poli_id_from_url = $_GET['poli_id'] ?? null;
$dokter_id_from_url = $_GET['dokter_id'] ?? null;
$tanggal_from_url = $_GET['tanggal'] ?? null;

// Ambil data pasien
if ($pasien_id_from_url) {
    $q = mysqli_query($conn, "SELECT * FROM pasien WHERE id = " . (int)$pasien_id_from_url);
    if ($q && mysqli_num_rows($q) > 0) {
        $pasien = mysqli_fetch_assoc($q);
    }
}

// Ambil info poli
if ($poli_id_from_url) {
    $q = mysqli_query($conn, "SELECT nama FROM poli WHERE id = " . (int)$poli_id_from_url);
    if ($q && mysqli_num_rows($q) > 0) {
        $poli_info = mysqli_fetch_assoc($q);
    }
}

// Ambil info dokter
if ($dokter_id_from_url) {
    $q = mysqli_query($conn, "SELECT nama, spesialisasi FROM dokter WHERE id = " . (int)$dokter_id_from_url);
    if ($q && mysqli_num_rows($q) > 0) {
        $dokter_info = mysqli_fetch_assoc($q);
    }
}

/* ===== VARIABEL ===== */
$pesan = '';
$keyword = $_POST['keyword'] ?? '';
$poli_id = $_POST['poli_id'] ?? '';
$dokter_id = $_POST['dokter_id'] ?? '';
$tanggal = $tanggal_from_url 
            ?? ($_POST['tanggal'] ?? date('Y-m-d'));

/* ===== CARI PASIEN ===== */
if (isset($_POST['cari_pasien'])) {
    $key = mysqli_real_escape_string($conn, trim($keyword));
    if ($key !== '') {
        $q = mysqli_query($conn, "SELECT * FROM pasien WHERE nik LIKE '%$key%' OR nama LIKE '%$key%' OR no_rm LIKE '%$key%' LIMIT 1");
        if (mysqli_num_rows($q) > 0) {
            $pasien = mysqli_fetch_assoc($q);
            header("Location: ../RegisterPasien/register_pasien.php?step=2&pasien_id=" . $pasien['id']);
            exit;
        } else {
            $pesan = "Pasien tidak ditemukan.";
        }
    } else {
        $pesan = "Masukkan kata kunci pencarian!";
    }
}

/* ===== DAFTAR PASIEN BARU ===== */
if (isset($_POST['daftar_baru'])) {
    $nama      = trim($_POST['nama'] ?? '');
    $nik       = trim($_POST['nik'] ?? '');
    $tgl_lahir = $_POST['tgl_lahir'] ?? '';
    $no_hp     = trim($_POST['no_hp'] ?? '');
    $alamat    = trim($_POST['alamat'] ?? '');

    if (!$nama || !$nik || !$tgl_lahir) {
        $pesan = "Nama, NIK, dan Tanggal Lahir wajib diisi!";
    } else {
        $cek = mysqli_query($conn, "SELECT id FROM pasien WHERE nik = '" . mysqli_real_escape_string($conn, $nik) . "'");
        if (mysqli_num_rows($cek) > 0) {
            $pesan = "NIK sudah terdaftar!";
        } else {
            $tahun = date('Y');
            $q = mysqli_query($conn, "SELECT no_rm FROM pasien WHERE no_rm LIKE 'RM-$tahun-%' ORDER BY no_rm DESC LIMIT 1");
            $urut = 1;
            if (mysqli_num_rows($q) > 0) {
                $d = mysqli_fetch_assoc($q);
                $last = (int) substr($d['no_rm'], -4);
                $urut = $last + 1;
            }
            $no_rm = 'RM-' . $tahun . '-' . str_pad($urut, 4, '0', STR_PAD_LEFT);
            
            $nama_esc = mysqli_real_escape_string($conn, $nama);
            $nik_esc = mysqli_real_escape_string($conn, $nik);
            $tgl_lahir_esc = mysqli_real_escape_string($conn, $tgl_lahir);
            $no_hp_esc = mysqli_real_escape_string($conn, $no_hp);
            $alamat_esc = mysqli_real_escape_string($conn, $alamat);

            $insert = mysqli_query($conn, 
                "INSERT INTO pasien (no_rm, nama, nik, tgl_lahir, no_hp, alamat) 
                 VALUES ('$no_rm','$nama_esc','$nik_esc','$tgl_lahir_esc','$no_hp_esc','$alamat_esc')"
            );
            
            if ($insert) {
                $pasien_id = mysqli_insert_id($conn);
                header("Location: ../RegisterPasien/register_pasien.php?step=2&pasien_id=$pasien_id");
                exit;
            } else {
                $pesan = "Gagal menyimpan pasien.";
            }
        }
    }
}

/* ===== AMBIL DATA POLI & DOKTER ===== */
$poli_list = [];
$result_poli = mysqli_query($conn, "SELECT id, nama FROM poli WHERE status = 'active'");
if ($result_poli) {
    while ($row = mysqli_fetch_assoc($result_poli)) {
        $poli_list[] = $row;
    }
}

$dokter_list = [];
$result_dokter = mysqli_query($conn, "SELECT id, nama, spesialisasi FROM dokter WHERE status = 'active'");
if ($result_dokter) {
    while ($row = mysqli_fetch_assoc($result_dokter)) {
        $dokter_list[] = $row;
    }
}

/* ===== DAFTAR PASIEN KE POLI ===== */
if (isset($_POST['daftar_antrian']) && $pasien) {
    $pasien_id = (int)$pasien['id'];
    $poli_id = (int)($_POST['poli_id'] ?? 0);
    $dokter_id = (int)($_POST['dokter_id'] ?? 0);
    $tanggal = mysqli_real_escape_string($conn, $_POST['tanggal'] ?? date('Y-m-d'));

    if ($poli_id <= 0 || $dokter_id <= 0) {
        $pesan = "Pilih Poli dan Dokter yang valid!";
    } else {
        // ✅ VALIDASI JADWAL DOKTER (BARU - SAMA SEPERTI DI HALAMAN PASIEN)
        $hari = date('l', strtotime($tanggal));
        $hari_map = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu'
        ];
        $hari_indo = $hari_map[$hari] ?? $hari;
        
        // Cek apakah dokter tersedia pada hari tersebut di poli yang dipilih
        $cek_jadwal = mysqli_query($conn, 
            "SELECT * FROM jadwal_dokter 
             WHERE dokter_id = $dokter_id 
             AND poli_id = $poli_id 
             AND hari = '$hari_indo'"
        );
        
        if (mysqli_num_rows($cek_jadwal) == 0) {
            $pesan = "Dokter tidak tersedia pada tanggal tersebut untuk poli yang dipilih! Silakan pilih tanggal atau dokter lain.";
        } else {
            // Cek duplikat antrian
            $cek = mysqli_query($conn, "SELECT id FROM pendaftaran WHERE pasien_id = '$pasien_id' AND tanggal = '$tanggal'");
            if (mysqli_num_rows($cek) > 0) {
                $pesan = "Pasien sudah terdaftar pada tanggal ini!";
            } else {
                // Generate nomor antrian
                $q = mysqli_query($conn, "SELECT COUNT(*) AS total FROM pendaftaran WHERE poli_id = '$poli_id' AND tanggal = '$tanggal'");
                $d = mysqli_fetch_assoc($q);
                $no = (int)$d['total'] + 1;
                $huruf = chr(64 + $poli_id);
                $no_antrian = $huruf . str_pad($no, 3, '0', STR_PAD_LEFT);

                // Insert ke database
                $insert = mysqli_query($conn, 
                    "INSERT INTO pendaftaran (pasien_id, dokter_id, poli_id, tanggal, no_antrian, status) 
                     VALUES ('$pasien_id', '$dokter_id', '$poli_id', '$tanggal', '$no_antrian', 'menunggu')"
                );

                if ($insert) {
                    header("Location: ../RegisterPasien/register_pasien.php?step=3&pasien_id=$pasien_id&poli_id=$poli_id&dokter_id=$dokter_id&tanggal=$tanggal&antrian=" . urlencode($no_antrian));
                    exit;
                } else {
                    $pesan = "Gagal mendaftarkan antrian.";
                }
            }
        }
    }
}

// Ambil pesan dari URL jika ada
if (isset($_GET['antrian'])) {
    $pesan = "Pendaftaran berhasil! Nomor Antrian: <strong>" . htmlspecialchars($_GET['antrian']) . "</strong>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Pasien - Klinik Sehat</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --secondary: #64748b;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --light: #f8fafc;
            --white: #ffffff;
            --text-dark: #1e293b;
            --text-gray: #64748b;
            --border: #e2e8f0;
            --shadow: 0 1px 3px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 25px rgba(0,0,0,0.1);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--light);
            color: var(--text-dark);
            line-height: 1.6;
        }

        /* Header */
        .header {
            background: var(--white);
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .logo i {
            font-size: 1.75rem;
            color: var(--primary);
        }

        .logo-text h1 {
            font-size: 1.25rem;
            color: var(--text-dark);
            font-weight: 700;
        }

        .logo-text p {
            font-size: 0.75rem;
            color: var(--text-gray);
        }

        .user-section {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .user-info {
            text-align: right;
        }

        .user-info .name {
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-dark);
        }

        .user-info .role {
            font-size: 0.75rem;
            color: var(--text-gray);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        /* Main Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        /* Page Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid var(--border);
        }

        .page-title h2 {
            font-size: 1.75rem;
            color: var(--text-dark);
            margin-bottom: 0.25rem;
        }

        .page-title p {
            color: var(--text-gray);
            font-size: 0.875rem;
        }

        .btn-back {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            background: var(--white);
            color: var(--text-dark);
            border: 2px solid var(--border);
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-back:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        /* Steps */
        .steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            position: relative;
        }

        .steps::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--border);
            z-index: 0;
        }

        .step {
            flex: 1;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--white);
            border: 2px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.5rem;
            font-weight: 600;
            color: var(--text-gray);
        }

        .step.active .step-circle {
            background: var(--primary);
            border-color: var(--primary);
            color: var(--white);
        }

        .step.completed .step-circle {
            background: var(--success);
            border-color: var(--success);
            color: var(--white);
        }

        .step-label {
            font-size: 0.8125rem;
            color: var(--text-gray);
        }

        .step.active .step-label {
            color: var(--primary);
            font-weight: 600;
        }

        /* Card */
        .card {
            background: var(--white);
            border-radius: 12px;
            padding: 2rem;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }

        .card-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--light);
        }

        .card-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: rgba(37, 99, 235, 0.1);
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .card-header h3 {
            font-size: 1.125rem;
            color: var(--text-dark);
        }

        /* Search Box */
        .search-section {
            background: var(--light);
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }

        .search-box {
            position: relative;
            margin-bottom: 1rem;
        }

        .search-box input {
            width: 100%;
            padding: 0.875rem 1rem 0.875rem 2.75rem;
            border: 2px solid var(--border);
            border-radius: 8px;
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }

        .search-box input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .search-box i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-gray);
        }

        .search-buttons {
            display: flex;
            gap: 0.75rem;
        }

        /* Patient Result */
        .patient-result {
            background: var(--white);
            padding: 1.5rem;
            border-radius: 10px;
            border: 2px solid var(--success);
            margin-bottom: 1.5rem;
        }

        .patient-result-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .patient-avatar {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 700;
        }

        .patient-name {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.25rem;
        }

        .patient-rm {
            font-size: 0.8125rem;
            color: var(--text-gray);
        }

        .patient-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 0.75rem;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
        }

        .detail-item i {
            color: var(--text-gray);
            width: 20px;
        }

        /* Form */
        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        .form-group label .required {
            color: var(--danger);
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid var(--border);
            border-radius: 8px;
            font-size: 0.875rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        select.form-control {
            cursor: pointer;
        }

        textarea.form-control {
            min-height: 100px;
            resize: vertical;
            font-family: inherit;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        /* Button */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-primary {
            background: var(--primary);
            color: var(--white);
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn-success {
            background: var(--success);
            color: var(--white);
        }

        .btn-success:hover {
            background: #059669;
            transform: translateY(-2px);
        }

        .btn-outline {
            background: var(--white);
            color: var(--text-dark);
            border: 2px solid var(--border);
        }

        .btn-outline:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 2px solid var(--light);
        }

        /* Alert */
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-info {
            background: rgba(6, 182, 212, 0.1);
            color: #0891b2;
            border: 1px solid rgba(6, 182, 212, 0.3);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-container {
                padding: 1rem;
            }

            .container {
                padding: 1rem;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .patient-details {
                grid-template-columns: 1fr;
            }

            .steps {
                flex-direction: column;
                gap: 1rem;
            }

            .steps::before {
                display: none;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn-back {
                width: 100%;
                justify-content: center;
            }

            .user-info {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-container">
            <div class="logo">
                <i class="fas fa-hospital"></i>
                <div class="logo-text">
                    <h1>Klinik Sehat</h1>
                    <p>Sistem Manajemen Klinik</p>
                </div>
            </div>
            <div class="user-section">
                <div class="user-info">
                    <div class="name"><?= ucfirst($_SESSION['role']) ?></div>
                    <div class="role">Administrator</div>
                </div>
                <div class="user-avatar">
                    <i class="fas fa-user"></i>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="container">

        <!-- Page Header -->
        <div class="page-header">
            <div class="page-title">
                <h2>Registrasi Pasien</h2>
                <p>Daftarkan pasien untuk pemeriksaan hari ini</p>
            </div>
            <a href="../Dashboard/dashboard.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>

        <!-- Steps -->
        <div class="steps">
            <div class="step <?= $step >= 1 ? ($step > 1 ? 'completed' : 'active') : '' ?>">
                <div class="step-circle"><?= $step > 1 ? '<i class="fas fa-check"></i>' : '1' ?></div>
                <div class="step-label">Cari Pasien</div>
            </div>
            <div class="step <?= $step == 2 ? 'active' : ($step > 2 ? 'completed' : '') ?>">
                <div class="step-circle">2</div>
                <div class="step-label">Pilih Poli & Dokter</div>
            </div>
            <div class="step <?= $step == 3 ? 'active' : '' ?>">
                <div class="step-circle">3</div>
                <div class="step-label">Konfirmasi</div>
            </div>
        </div>

        <?php if ($pesan): ?>
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <span><?= $pesan ?></span>
        </div>
        <?php endif; ?>

        <!-- Step 1: Search & New Patient -->
        <?php if ($step == 1): ?>
        <div class="card">
            <div class="card-header">
                <div class="card-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h3>Cari Data Pasien</h3>
            </div>

            <div class="search-section">
                <form method="POST">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" name="keyword" placeholder="Cari berdasarkan NIK atau Nama Pasien..." value="<?= htmlspecialchars($keyword) ?>">
                    </div>
                    <div class="search-buttons">
                        <button type="submit" name="cari_pasien" class="btn btn-primary" style="flex: 1;">
                            <i class="fas fa-search"></i> Cari Pasien
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card" id="newPatientForm">
            <div class="card-header">
                <div class="card-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h3>Daftar Pasien Baru</h3>
            </div>
            <form method="POST">
                <div class="form-group">
                    <label for="nama">Nama Lengkap <span class="required">*</span></label>
                    <input type="text" class="form-control" id="nama" name="nama" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="nik">NIK <span class="required">*</span></label>
                        <input type="text" class="form-control" id="nik" name="nik" required>
                    </div>
                    <div class="form-group">
                        <label for="tgl_lahir">Tanggal Lahir <span class="required">*</span></label>
                        <input type="date" class="form-control" id="tgl_lahir" name="tgl_lahir" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="no_hp">No HP</label>
                        <input type="text" class="form-control" id="no_hp" name="no_hp">
                    </div>
                    <div class="form-group">
                        <label for="email">Email <small style="color: var(--text-gray);">(Opsional)</small></label>
                        <input type="email" class="form-control" id="email" name="email">
                    </div>
                </div>

                <div class="form-group">
                    <label for="alamat">Alamat</label>
                    <textarea class="form-control" id="alamat" name="alamat"></textarea>
                </div>
                <button type="submit" name="daftar_baru" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Pasien Baru
                </button>
            </form>
        </div>
        <?php endif; ?>

        <!-- Step 2: Pilih Poli & Dokter -->
        <?php if ($step == 2 && $pasien): ?>
        <div class="card">
            <div class="card-header">
                <div class="card-icon">
                    <i class="fas fa-clinic-medical"></i>
                </div>
                <h3>Pilih Poli dan Dokter</h3>
            </div>

            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <span>Pilih poli dan dokter yang sesuai dengan keluhan pasien</span>
            </div>

            <form method="POST">
                <input type="hidden" name="pasien_id" value="<?= (int)$pasien['id'] ?>">
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="tanggal">Tanggal Kunjungan <span class="required">*</span></label>
                        <input type="date" class="form-control" id="tanggal" name="tanggal" value="<?= htmlspecialchars($tanggal) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="poli">Poli <span class="required">*</span></label>
                        <select class="form-control" id="poli" name="poli_id" required>
                            <option value="">-- Pilih Poli --</option>
                            <?php foreach ($poli_list as $p): ?>
                                <option value="<?= (int)$p['id'] ?>" <?= ((int)$p['id'] === (int)$poli_id) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['nama']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="dokter">Dokter <span class="required">*</span></label>
                    <select class="form-control" id="dokter" name="dokter_id" required>
                        <option value="">-- Pilih Dokter --</option>
                        <?php foreach ($dokter_list as $d): ?>
                            <option value="<?= (int)$d['id'] ?>" <?= ((int)$d['id'] === (int)$dokter_id) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($d['nama']) ?> (<?= htmlspecialchars($d['spesialisasi']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-actions">
                    <a href="../RegisterPasien/register_pasien.php?step=1" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                    <button type="submit" name="daftar_antrian" class="btn btn-success" style="flex: 1;">
                        <i class="fas fa-check"></i> Daftarkan Pasien
                    </button>
                </div>
            </form>
        </div>
        <?php endif; ?>

        <!-- Step 3: Konfirmasi -->
        <?php if ($step == 3 && $pasien): ?>
        <div class="card">
            <div class="card-header">
                <div class="card-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h3>Konfirmasi Pendaftaran</h3>
            </div>

            <div class="patient-result">
                <div class="patient-result-header">
                    <div class="patient-avatar"><?= strtoupper(substr($pasien['nama'], 0, 2)) ?></div>
                    <div>
                        <div class="patient-name"><?= htmlspecialchars($pasien['nama']) ?></div>
                        <div class="patient-rm">No. RM: <?= htmlspecialchars($pasien['no_rm']) ?></div>
                    </div>
                </div>
                <div class="patient-details">
                    <div class="detail-item"><i class="fas fa-id-card"></i> <?= htmlspecialchars($pasien['nik']) ?></div>
                    <div class="detail-item"><i class="fas fa-phone"></i> <?= htmlspecialchars($pasien['no_hp'] ?: '-') ?></div>
                    <div class="detail-item"><i class="fas fa-birthday-cake"></i> <?= date('d F Y', strtotime($pasien['tgl_lahir'])) ?></div>
                    <div class="detail-item"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($pasien['alamat'] ?: '-') ?></div>
                    
                    <div class="detail-item">
                        <i class="fas fa-calendar-alt"></i> 
                        <span>Tanggal Kunjungan: <strong><?= date('d F Y', strtotime($tanggal)) ?></strong></span>
                    </div>
                    
                    <?php if ($poli_info): ?>
                    <div class="detail-item"><i class="fas fa-clinic-medical"></i> Poli: <?= htmlspecialchars($poli_info['nama']) ?></div>
                    <?php endif; ?>
                    
                    <?php if ($dokter_info): ?>
                    <div class="detail-item"><i class="fas fa-user-md"></i> Dokter: <?= htmlspecialchars($dokter_info['nama']) ?> (<?= htmlspecialchars($dokter_info['spesialisasi']) ?>)</div>
                    <?php endif; ?>
                    
                    <?php if (isset($_GET['antrian'])): ?>
                    <div class="detail-item"><i class="fas fa-ticket-alt"></i> No. Antrian: <?= htmlspecialchars($_GET['antrian']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="alert alert-info">
                <i class="fas fa-check-circle"></i>
                <span>Pendaftaran pasien berhasil! Silakan cetak atau simpan informasi ini.</span>
            </div>

            <div class="form-actions">
                <a href="../RegisterPasien/register_pasien.php?step=1" class="btn btn-outline">
                    <i class="fas fa-home"></i> Kembali ke Registrasi
                </a>
                <a href="../Dashboard/dashboard.php" class="btn btn-success" style="flex: 1;">
                    <i class="fas fa-plus"></i> Daftarkan Pasien Baru
                </a>
            </div>
        </div>
        <?php endif; ?>
    </main>
</body>
</html>