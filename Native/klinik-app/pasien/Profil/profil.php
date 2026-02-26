<?php
session_start();
include '../../konfigurasi/koneksi.php';

/* ===== PROTEKSI PASIEN ===== */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'pasien') {
    session_destroy();
    header("Location: ../../auth/login.php?error=access_denied");
    exit;
}

$pasien_id = $_SESSION['pasien_id'] ?? 0;

if (empty($pasien_id)) {
    session_destroy();
    header("Location: ../../auth/login.php?error=invalid_session");
    exit;
}

// Ambil data pasien
$query_patient = "SELECT p.*, u.email as user_email 
                  FROM pasien p 
                  LEFT JOIN users u ON p.user_id = u.id 
                  WHERE p.id = ?";
$stmt = $conn->prepare($query_patient);
$stmt->bind_param("i", $pasien_id);
$stmt->execute();
$patient = $stmt->get_result()->fetch_assoc();

if (!$patient) {
    session_destroy();
    header("Location: ../../auth/login.php?error=user_not_found");
    exit;
}

// Variabel untuk pesan
$success = '';
$error = '';

// Proses update profil
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profil'])) {
    $nama = trim($_POST['nama'] ?? '');
    $nik = trim($_POST['nik'] ?? '');
    $tgl_lahir = trim($_POST['tgl_lahir'] ?? '');
    $no_hp = trim($_POST['no_hp'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $alamat = trim($_POST['alamat'] ?? '');
    
    // Validasi input
    if (empty($nama) || empty($nik) || empty($tgl_lahir) || empty($no_hp) || empty($email) || empty($alamat)) {
        $error = 'Semua field wajib diisi!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid!';
    } else {
        // Cek apakah email sudah digunakan oleh user lain
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $patient['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = 'Email sudah digunakan oleh akun lain!';
        } else {
            // Update tabel pasien
            $stmt = $conn->prepare("UPDATE pasien SET nama = ?, nik = ?, tgl_lahir = ?, no_hp = ?, email = ?, alamat = ? WHERE id = ?");
            $stmt->bind_param("ssssssi", $nama, $nik, $tgl_lahir, $no_hp, $email, $alamat, $pasien_id);
            
            if ($stmt->execute()) {
                // Update email di tabel users
                $stmt = $conn->prepare("UPDATE users SET email = ? WHERE id = ?");
                $stmt->bind_param("si", $email, $patient['user_id']);
                $stmt->execute();
                
                // Update session
                $_SESSION['nama'] = $nama;
                
                // Refresh data
                $stmt = $conn->prepare($query_patient);
                $stmt->bind_param("i", $pasien_id);
                $stmt->execute();
                $patient = $stmt->get_result()->fetch_assoc();
                
                $success = 'Profil berhasil diperbarui!';
            } else {
                $error = 'Terjadi kesalahan saat memperbarui profil!';
            }
        }
    }
}

// Proses ganti password (TANPA PASSWORD LAMA)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $password_baru = $_POST['password_baru'] ?? '';
    $konfirmasi_password = $_POST['konfirmasi_password'] ?? '';
    
    // Validasi
    if (empty($password_baru) || empty($konfirmasi_password)) {
        $error = 'Semua field password wajib diisi!';
    } elseif (strlen($password_baru) < 6) {
        $error = 'Password minimal 6 karakter!';
    } elseif ($password_baru !== $konfirmasi_password) {
        $error = 'Password baru dan konfirmasi tidak sama!';
    } else {
        // Update password baru LANGSUNG (tanpa verifikasi password lama)
        $password_hash = password_hash($password_baru, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $password_hash, $patient['user_id']);
        
        if ($stmt->execute()) {
            $success = 'Password berhasil diubah!';
        } else {
            $error = 'Terjadi kesalahan saat mengubah password!';
        }
    }
}

// Proses hapus akun
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_account'])) {
    // Hapus data pendaftaran terkait terlebih dahulu (agar tidak error foreign key)
    $stmt = $conn->prepare("DELETE FROM pendaftaran WHERE pasien_id = ?");
    $stmt->bind_param("i", $pasien_id);
    $stmt->execute();
    
    // Hapus data pasien
    $stmt = $conn->prepare("DELETE FROM pasien WHERE id = ?");
    $stmt->bind_param("i", $pasien_id);
    $stmt->execute();
    
    // Hapus data user jika ada
    if ($patient['user_id']) {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $patient['user_id']);
        $stmt->execute();
    }
    
    // Logout dan redirect
    session_destroy();
    header("Location: ../../auth/login.php?success=account_deleted");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - Klinik Sehat</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ... CSS SAMA SEPERTI SEBELUMNYA ... */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
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
        .logout-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(239, 68, 68, 0.12);
            color: var(--danger);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: 0.25s;
            text-decoration: none;
        }
        .logout-btn:hover {
            background: var(--danger);
            color: var(--white);
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 2rem;
        }
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid var(--border);
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
        .card {
            background: var(--white);
            border-radius: 12px;
            padding: 2rem;
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
        }
        .card-header {
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .card-header h2 {
            font-size: 1.5rem;
            color: var(--text-dark);
            font-weight: 700;
        }
        .card-header i {
            color: var(--primary);
            font-size: 1.25rem;
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
        .alert i {
            font-size: 1.25rem;
        }
        .alert-success {
            background: #ecfdf5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }
        .alert-error {
            background: #fef2f2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }
        /* Form */
        .form-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }
        @media (max-width: 968px) {
            .form-section {
                grid-template-columns: 1fr;
            }
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
            font-weight: 600;
            font-size: 0.9rem;
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid var(--border);
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
        }
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        .form-note {
            font-size: 0.85rem;
            color: var(--text-gray);
            margin-top: 0.5rem;
            font-style: italic;
        }
        /* Profile Info */
        .profile-info {
            display: grid;
            grid-template-columns: 150px 1fr;
            gap: 1.5rem;
            padding: 1.5rem;
            background: var(--light);
            border-radius: 12px;
            margin-bottom: 1.5rem;
        }
        .profile-label {
            font-weight: 600;
            color: var(--text-gray);
            font-size: 0.9rem;
        }
        .profile-value {
            color: var(--text-dark);
            font-weight: 600;
            font-size: 1rem;
        }
        /* Button Group */
        .btn-group {
            display: flex;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }
        .btn-primary {
            background: var(--primary);
            color: var(--white);
        }
        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
        }
        .btn-secondary {
            background: var(--text-gray);
            color: var(--white);
        }
        .btn-secondary:hover {
            background: #475569;
        }
        .btn-outline {
            background: var(--white);
            color: var(--primary);
            border: 2px solid var(--primary);
        }
        .btn-outline:hover {
            background: var(--primary);
            color: var(--white);
        }
        .btn-danger {
            background: var(--danger);
            color: var(--white);
        }
        .btn-danger:hover {
            background: #dc2626;
            transform: translateY(-2px);
        }
        /* Password Section */
        .password-section {
            border-top: 2px solid var(--border);
            padding-top: 2rem;
            margin-top: 2rem;
        }
        /* Delete Account Section */
        .delete-section {
            border-top: 2px solid var(--border);
            padding-top: 2rem;
            margin-top: 2rem;
            background: linear-gradient(135deg, #fef2f2 0%, #fff 100%);
            border-radius: 12px;
            padding: 2rem;
        }
        .delete-warning {
            background: #fef2f2;
            border-left: 4px solid var(--danger);
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        .delete-warning h4 {
            color: var(--danger);
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .delete-warning ul {
            margin-left: 1.5rem;
            color: var(--danger);
            margin-top: 0.5rem;
        }
        .delete-warning li {
            margin-bottom: 0.25rem;
        }
        .delete-warning strong {
            font-weight: 700;
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
            .profile-info {
                grid-template-columns: 1fr;
            }
            .btn-group {
                flex-direction: column;
            }
            .btn {
                width: 100%;
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
                    <div class="name"><?php echo htmlspecialchars($patient['nama']); ?></div>
                    <div class="role">Pasien</div>
                </div>
                <div class="user-avatar">
                    <?php echo strtoupper(substr($patient['nama'], 0, 2)); ?>
                </div>
                <a href="../../auth/logout.php" class="logout-btn" title="Logout">
                    <i class="fas fa-right-from-bracket"></i>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fas fa-user"></i> Profil Saya</h1>
            <a href="../dashboard.php" class="btn-back">
                <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
            </a>
        </div>

        <!-- Alert Messages -->
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span><?php echo htmlspecialchars($success); ?></span>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo htmlspecialchars($error); ?></span>
            </div>
        <?php endif; ?>

        <!-- Card -->
        <div class="card">
            <div class="card-header">
                <i class="fas fa-id-card"></i>
                <h2>Data Pribadi</h2>
            </div>

            <!-- Profile Info (Read-only) -->
            <div style="margin-bottom: 2rem;">
                <h3 style="font-size: 1.125rem; margin-bottom: 1rem; color: var(--text-dark);">
                    <i class="fas fa-info-circle"></i> Informasi Profil
                </h3>
                <div class="profile-info">
                    <div class="profile-label">No. Rekam Medis</div>
                    <div class="profile-value"><?php echo htmlspecialchars($patient['no_rm']); ?></div>
                </div>
            </div>

            <!-- Edit Form -->
            <form method="POST" action="">
                <div class="form-section">
                    <div>
                        <div class="form-group">
                            <label for="nama">Nama Lengkap <span style="color: var(--danger);">*</span></label>
                            <input type="text" id="nama" name="nama" 
                                   value="<?php echo htmlspecialchars($patient['nama']); ?>" 
                                   required>
                        </div>

                        <div class="form-group">
                            <label for="nik">NIK <span style="color: var(--danger);">*</span></label>
                            <input type="text" id="nik" name="nik" 
                                   value="<?php echo htmlspecialchars($patient['nik']); ?>" 
                                   required maxlength="16">
                            <small class="form-note">Nomor Induk Kependudukan (16 digit)</small>
                        </div>

                        <div class="form-group">
                            <label for="tgl_lahir">Tanggal Lahir <span style="color: var(--danger);">*</span></label>
                            <input type="date" id="tgl_lahir" name="tgl_lahir" 
                                   value="<?php echo htmlspecialchars($patient['tgl_lahir']); ?>" 
                                   required>
                        </div>

                        <div class="form-group">
                            <label for="no_hp">Nomor HP <span style="color: var(--danger);">*</span></label>
                            <input type="tel" id="no_hp" name="no_hp" 
                                   value="<?php echo htmlspecialchars($patient['no_hp']); ?>" 
                                   required>
                            <small class="form-note">Contoh: 081234567890</small>
                        </div>
                    </div>

                    <div>
                        <div class="form-group">
                            <label for="email">Email <span style="color: var(--danger);">*</span></label>
                            <input type="email" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($patient['user_email'] ?? $patient['email']); ?>" 
                                   required>
                            <small class="form-note">Email digunakan untuk login</small>
                        </div>

                        <div class="form-group">
                            <label for="alamat">Alamat <span style="color: var(--danger);">*</span></label>
                            <textarea id="alamat" name="alamat" required><?php echo htmlspecialchars($patient['alamat']); ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="btn-group">
                    <button type="submit" name="update_profil" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                    <button type="reset" class="btn btn-secondary">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                </div>
            </form>

            <!-- Change Password Section (TANPA PASSWORD LAMA) -->
            <div class="password-section">
                <div class="card-header">
                    <i class="fas fa-lock"></i>
                    <h2>Ganti Password</h2>
                </div>
                
                <form method="POST" action="">
                    <div class="form-section">
                        <div>
                            <div class="form-group">
                                <label for="password_baru">Password Baru <span style="color: var(--danger);">*</span></label>
                                <input type="password" id="password_baru" name="password_baru" required minlength="6">
                                <small class="form-note">Minimal 6 karakter</small>
                            </div>
                        </div>

                        <div>
                            <div class="form-group">
                                <label for="konfirmasi_password">Konfirmasi Password <span style="color: var(--danger);">*</span></label>
                                <input type="password" id="konfirmasi_password" name="konfirmasi_password" required minlength="6">
                            </div>
                        </div>
                    </div>

                    <div class="btn-group">
                        <button type="submit" name="change_password" class="btn btn-outline">
                            <i class="fas fa-key"></i> Ubah Password
                        </button>
                    </div>
                </form>
            </div>

            <!-- Delete Account Section -->
            <div class="delete-section">
                <div class="card-header">
                    <i class="fas fa-trash-alt"></i>
                    <h2>Hapus Akun</h2>
                </div>
                
                <div class="delete-warning">
                    <h4><i class="fas fa-exclamation-triangle"></i> Peringatan Penting!</h4>
                    <p>Menghapus akun akan menghapus <strong>SEMUA DATA ANDA</strong> secara permanen, termasuk:</p>
                    <ul>
                        <li>Data profil pribadi</li>
                        <li>Riwayat kunjungan dan antrian</li>
                        <li>Akun login Anda</li>
                        <li>Semua data terkait di sistem klinik</li>
                    </ul>
                    <p style="margin-top: 1rem; font-weight: 600;">
                        <i class="fas fa-exclamation-circle"></i> Tindakan ini <strong>TIDAK DAPAT DIBATALKAN!</strong>
                    </p>
                </div>

                <form method="POST" action="" onsubmit="return confirm('Apakah Anda YAKIN ingin menghapus akun? Semua data akan hilang PERMANEN!');">
                    <button type="submit" name="delete_account" class="btn btn-danger">
                        <i class="fas fa-trash-alt"></i> Hapus Akun Saya
                    </button>
                </form>
            </div>
        </div>
    </main>
</body>
</html>