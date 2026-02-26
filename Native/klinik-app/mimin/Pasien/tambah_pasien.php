<?php
session_start();
// CEK LOGIN (sesuaikan dengan sistemmu)
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

// KONEKSI DATABASE
include '../../konfigurasi/koneksi.php';

// INISIALISASI VARIABEL
$pesan = '';
$nama = '';
$nik = '';
$tgl_lahir = '';
$no_hp = '';
$alamat = '';
$email = ''; // Opsional

// PROSES FORM TAMBAH PASIEN
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama      = trim($_POST['nama'] ?? '');
    $nik       = trim($_POST['nik'] ?? '');
    $tgl_lahir = $_POST['tgl_lahir'] ?? '';
    $no_hp     = trim($_POST['no_hp'] ?? '');
    $alamat    = trim($_POST['alamat'] ?? '');
    $email     = trim($_POST['email'] ?? '');

    // VALIDASI
    if (!$nama || !$nik || !$tgl_lahir || !$no_hp) {
        $pesan = "Nama, NIK, Tanggal Lahir, dan No. HP wajib diisi!";
    } else {
        // CEK NIK SUDAH ADA
        $cek_nik = mysqli_query($conn, "SELECT id FROM pasien WHERE nik = '" . mysqli_real_escape_string($conn, $nik) . "'");
        if (mysqli_num_rows($cek_nik) > 0) {
            $pesan = "NIK sudah terdaftar! Silakan gunakan NIK yang berbeda.";
        } else {
            // GENERATE NO. RM
            $tahun = date('Y');
            $q = mysqli_query($conn, "SELECT no_rm FROM pasien WHERE no_rm LIKE 'RM-$tahun-%' ORDER BY no_rm DESC LIMIT 1");
            $urut = 1;
            if (mysqli_num_rows($q) > 0) {
                $d = mysqli_fetch_assoc($q);
                $last = (int) substr($d['no_rm'], -4);
                $urut = $last + 1;
            }
            $no_rm = 'RM-' . $tahun . '-' . str_pad($urut, 4, '0', STR_PAD_LEFT);
            
            // ESCAPE DATA
            $nama_esc      = mysqli_real_escape_string($conn, $nama);
            $nik_esc       = mysqli_real_escape_string($conn, $nik);
            $tgl_lahir_esc = mysqli_real_escape_string($conn, $tgl_lahir);
            $no_hp_esc     = mysqli_real_escape_string($conn, $no_hp);
            $alamat_esc    = mysqli_real_escape_string($conn, $alamat);
            $email_esc     = mysqli_real_escape_string($conn, $email);

            // SIMPAN KE DATABASE
            $insert = mysqli_query($conn, 
                "INSERT INTO pasien (no_rm, nama, nik, tgl_lahir, no_hp, alamat, email) 
                 VALUES ('$no_rm','$nama_esc','$nik_esc','$tgl_lahir_esc','$no_hp_esc','$alamat_esc','$email_esc')"
            );
            
            if ($insert) {
                $_SESSION['success_message'] = "Pasien baru berhasil didaftarkan!";
                // REDIRECT KE HALAMAN DATA PASIEN
                header("Location: ../Pasien/pasien.php");
                exit;
            } else {
                $pesan = "Gagal menyimpan data pasien: " . mysqli_error($conn);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Pasien - Klinik Sehat</title>
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
            flex-wrap: wrap;
            gap: 1rem;
        }

        .page-title {
            display: flex;
            flex-direction: column;
        }

        .page-title h2 {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.25rem;
        }

        .page-subtitle {
            font-size: 1rem;
            color: var(--text-gray);
            font-weight: 500;
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

        /* Card */
        .card {
            background: var(--white);
            border-radius: 12px;
            padding: 2rem;
            box-shadow: var(--shadow);
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

        .btn-submit {
            flex: 1;
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

        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            color: var(--danger);
            border: 1px solid rgba(239, 68, 68, 0.3);
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
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .user-info {
                display: none;
            }
        }
    </style>
</head>
<body>
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
                <div class="user-avatar"><i class="fas fa-user"></i></div>
            </div>
        </div>
    </header>

    <main class="container">
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-title">
                <h2>Tambah Pasien Baru</h2>
                <div class="page-subtitle">Isi data pasien baru dengan lengkap</div>
            </div>
            <a href="../Pasien/pasien.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Kembali ke Data Pasien
            </a>
        </div>

        <?php if ($pesan): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i>
            <span><?= $pesan ?></span>
        </div>
        <?php endif; ?>

        <!-- Form Tambah Pasien -->
        <div class="card">
            <div class="card-header">
                <div class="card-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h3>Data Pasien Baru</h3>
            </div>
            <form method="POST">
                <div class="form-group">
                    <label for="nama">Nama Lengkap <span class="required">*</span></label>
                    <input type="text" class="form-control" id="nama" name="nama" value="<?= htmlspecialchars($nama) ?>" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="nik">NIK <span class="required">*</span></label>
                        <input type="text" class="form-control" id="nik" name="nik" value="<?= htmlspecialchars($nik) ?>" maxlength="16" required>
                        <div class="form-hint" style="font-size: 0.8125rem; color: var(--text-gray); margin-top: 0.25rem;">
                            Nomor Induk Kependudukan (16 digit)
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="tgl_lahir">Tanggal Lahir <span class="required">*</span></label>
                        <input type="date" class="form-control" id="tgl_lahir" name="tgl_lahir" value="<?= htmlspecialchars($tgl_lahir) ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="no_hp">No. HP <span class="required">*</span></label>
                        <input type="tel" class="form-control" id="no_hp" name="no_hp" value="<?= htmlspecialchars($no_hp) ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email <small style="color: var(--text-gray);">(Opsional)</small></label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($email) ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="alamat">Alamat Lengkap <span class="required">*</span></label>
                    <textarea class="form-control" id="alamat" name="alamat" required><?= htmlspecialchars($alamat) ?></textarea>
                </div>

                <div class="form-actions">
                    <!-- TOMBOL REFRESH (tanpa JS) -->
                    <a href="../Pasien/tambah_pasien.php" class="btn btn-outline">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </a>
                    <button type="submit" class="btn btn-primary btn-submit">
                        <i class="fas fa-save"></i> Simpan Data Pasien
                    </button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>