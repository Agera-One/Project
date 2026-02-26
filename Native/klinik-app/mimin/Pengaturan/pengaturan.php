<?php
session_start();
include '../../konfigurasi/koneksi.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../../auth/login.php");
    exit;
}

$pesan = '';
$error = '';

// ==========================================
// AMBIL DATA USER DARI DATABASE
// ==========================================
$user_id = $_SESSION['user_id'] ?? 0;
$user_data = [];
if ($user_id) {
    $result_user = mysqli_query($conn, "SELECT * FROM users WHERE id = " . intval($user_id));
    if ($result_user && mysqli_num_rows($result_user) > 0) {
        $user_data = mysqli_fetch_assoc($result_user);
    }
}

// ==========================================
// AMBIL SETTING KLINIK
// ==========================================
$settings = [];
$result = mysqli_query($conn, "SELECT setting_key, setting_value FROM setting");
while ($row = mysqli_fetch_assoc($result)) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Default jika belum ada
$defaults = [
    'nama_klinik'     => 'Klinik Sehat',
    'alamat'          => 'Jl. Sehat Sentosa No. 123, Semarang',
    'telepon'         => '024-1234567',
    'jam_operasional' => 'Senin – Sabtu, 08:00 – 20:00'
];

foreach ($defaults as $key => $default) {
    if (!isset($settings[$key])) {
        mysqli_query($conn, "
            INSERT INTO setting (setting_key, setting_value) 
            VALUES ('" . mysqli_real_escape_string($conn, $key) . "', '" . mysqli_real_escape_string($conn, $default) . "')
        ");
        $settings[$key] = $default;
    }
}

// ==========================================
// PROSES UPDATE PROFIL USER
// ==========================================
if (isset($_POST['update_profil'])) {
    $email = trim($_POST['email'] ?? '');
    $password_baru = trim($_POST['password_baru'] ?? '');
    $konfirmasi_password = trim($_POST['konfirmasi_password'] ?? '');
    
    if (!$email) {
        $error = "Email wajib diisi.";
    } else {
        // Validasi email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Format email tidak valid.";
        } else {
            // Cek apakah email sudah digunakan user lain
            $check_email = mysqli_query($conn, "
                SELECT id FROM users 
                WHERE email = '" . mysqli_real_escape_string($conn, $email) . "' 
                AND id != " . intval($user_id)
            );
            
            if (mysqli_num_rows($check_email) > 0) {
                $error = "Email sudah digunakan oleh akun lain.";
            } else {
                // Update data user
                $sql = "UPDATE users SET email = '" . mysqli_real_escape_string($conn, $email) . "'";
                
                // Jika password diisi, update juga (dengan validasi konfirmasi)
                if (!empty($password_baru) || !empty($konfirmasi_password)) {
                    // Validasi: kedua field harus diisi
                    if (empty($password_baru) || empty($konfirmasi_password)) {
                        $error = "Password baru dan konfirmasi password harus diisi bersamaan.";
                    } 
                    // Validasi: minimal 6 karakter
                    elseif (strlen($password_baru) < 6) {
                        $error = "Password minimal 6 karakter.";
                    } 
                    // Validasi: password harus sama
                    elseif ($password_baru !== $konfirmasi_password) {
                        $error = "Password baru dan konfirmasi password tidak sama.";
                    } else {
                        // Update password
                        $sql .= ", password = '" . password_hash($password_baru, PASSWORD_DEFAULT) . "'";
                    }
                }
                
                // Jika tidak ada error, jalankan query
                if (empty($error)) {
                    $sql .= " WHERE id = " . intval($user_id);
                    
                    if (mysqli_query($conn, $sql)) {
                        $pesan = "Profil berhasil diperbarui!";
                        
                        // Refresh data user
                        $result_user = mysqli_query($conn, "SELECT * FROM users WHERE id = " . intval($user_id));
                        $user_data = mysqli_fetch_assoc($result_user);
                    } else {
                        $error = "Gagal memperbarui profil.";
                    }
                }
            }
        }
    }
}

// ==========================================
// PROSES UPDATE INFORMASI KLINIK
// ==========================================
if (isset($_POST['update_klinik'])) {
    $alamat = trim($_POST['alamat'] ?? '');
    $telepon = trim($_POST['telepon'] ?? '');
    $jam = trim($_POST['jam_operasional'] ?? '');

    if (!$alamat || !$telepon || !$jam) {
        $error = "Semua kolom wajib diisi.";
    } else {
        // Update satu per satu
        $updates = [
            ['key' => 'alamat', 'value' => $alamat],
            ['key' => 'telepon', 'value' => $telepon],
            ['key' => 'jam_operasional', 'value' => $jam]
        ];

        $success = true;
        foreach ($updates as $u) {
            $sql = "UPDATE setting SET setting_value = '" . mysqli_real_escape_string($conn, $u['value']) . "' 
                    WHERE setting_key = '" . mysqli_real_escape_string($conn, $u['key']) . "'";
            if (!mysqli_query($conn, $sql)) {
                $success = false;
                break;
            }
        }

        if ($success) {
            $pesan = "Informasi klinik berhasil diperbarui!";
            // Refresh data
            $settings = [];
            $result = mysqli_query($conn, "SELECT setting_key, setting_value FROM setting");
            while ($row = mysqli_fetch_assoc($result)) {
                $settings[$row['setting_key']] = $row['setting_value'];
            }
        } else {
            $error = "Gagal memperbarui informasi.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan - Klinik Sehat</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
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
            --shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: var(--light);
            color: var(--text-dark);
            line-height: 1.6;
        }

        .container {
            max-width: 900px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid var(--border);
        }

        .page-title h1 {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-dark);
        }

        .page-title p {
            font-size: 0.875rem;
            color: var(--text-gray);
            margin-top: 0.5rem;
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
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--light);
        }

        .card-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: #e0e7ff;
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
        }

        .card h2 {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-dark);
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--text-dark);
        }

        .form-group label .required {
            color: var(--danger);
        }

        .form-control {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid var(--border);
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .form-control[disabled] {
            background: var(--light);
            cursor: not-allowed;
        }

        .form-control-password {
            position: relative;
        }

        .form-control-password input {
            padding-right: 2.5rem;
        }

        .toggle-password {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-gray);
            cursor: pointer;
            font-size: 1rem;
        }

        .toggle-password:hover {
            color: var(--primary);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
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
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .btn-secondary {
            background: var(--secondary);
            color: var(--white);
        }

        .btn-secondary:hover {
            background: #475569;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-success { 
            background: rgba(16, 185, 129, 0.1); 
            color: var(--success); 
            border-left: 4px solid var(--success);
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        .alert-error { 
            background: rgba(239, 68, 68, 0.1); 
            color: var(--danger); 
            border-left: 4px solid var(--danger);
            border: 1px solid rgba(239, 68, 68, 0.3);
        }

        .info-item {
            margin-bottom: 0.75rem;
            padding-left: 1rem;
            border-left: 3px solid var(--border);
        }

        .info-label {
            font-weight: 600;
            color: var(--text-gray);
        }

        .info-value {
            color: var(--text-dark);
        }

        .btn-group {
            display: flex;
            gap: 0.75rem;
            margin-top: 1.5rem;
        }

        .password-note {
            font-size: 0.875rem;
            color: var(--text-gray);
            margin-top: 0.5rem;
            font-style: italic;
        }

        .form-note {
            font-size: 0.875rem;
            color: var(--text-gray);
            margin-top: 0.5rem;
            display: block;
        }

        @media (max-width: 768px) {
            .container {
                margin: 1rem;
            }

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
            }

            .card {
                padding: 1.5rem;
            }

            .btn-group {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }

            .btn-back {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-title">
            <h1><i class="fas fa-cog"></i> Pengaturan</h1>
            <p>Kelola profil dan informasi klinik Anda</p>
        </div>
        <a href="../Dashboard/dashboard.php" class="btn-back">
            <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
        </a>
    </div>

    <!-- Alert Messages -->
    <?php if ($pesan): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <span><?= htmlspecialchars($pesan) ?></span>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <span><?= htmlspecialchars($error) ?></span>
        </div>
    <?php endif; ?>

    <!-- ==========================================
         PROFIL SAYA (CRUD LENGKAP)
    =========================================== -->
    <div class="card">
        <div class="card-header">
            <div class="card-icon">
                <i class="fas fa-user"></i>
            </div>
            <h2>Profil Saya</h2>
        </div>
        
        <form method="POST">
            <div class="form-group">
                <label for="email">Email <span class="required">*</span></label>
                <input type="email" class="form-control" id="email" name="email" 
                       value="<?= htmlspecialchars($user_data['email'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="password_baru">Password Baru</label>
                <div class="form-control-password">
                    <input type="password" class="form-control" id="password_baru" name="password_baru" 
                           placeholder="Minimal 6 karakter">
                    <button type="button" class="toggle-password" onclick="togglePassword('password_baru')">
                        <i class="fas fa-eye" id="icon-password_baru"></i>
                    </button>
                </div>
                <small class="password-note">Kosongkan jika tidak ingin mengubah password</small>
            </div>
            
            <div class="form-group">
                <label for="konfirmasi_password">Konfirmasi Password Baru</label>
                <div class="form-control-password">
                    <input type="password" class="form-control" id="konfirmasi_password" name="konfirmasi_password" 
                           placeholder="Ulangi password baru">
                    <button type="button" class="toggle-password" onclick="togglePassword('konfirmasi_password')">
                        <i class="fas fa-eye" id="icon-konfirmasi_password"></i>
                    </button>
                </div>
                <small class="password-note">Harus sama dengan password baru</small>
            </div>
            
            <div class="btn-group">
                <button type="submit" name="update_profil" class="btn btn-primary" a href="../Dashboard/dashboard.php">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
                <button type="reset" class="btn btn-secondary">
                    <i class="fas fa-undo"></i> Reset
                </button>
            </div>
        </form>
    </div>

    <!-- ==========================================
         INFORMASI KLINIK (CRUD LENGKAP)
    =========================================== -->
    <div class="card">
        <div class="card-header">
            <div class="card-icon">
                <i class="fas fa-hospital"></i>
            </div>
            <h2>Informasi Klinik</h2>
        </div>
        
        <form method="POST">
            <div class="form-group">
                <label for="nama_klinik">Nama Klinik</label>
                <input type="text" class="form-control" id="nama_klinik" name="nama_klinik" 
                       value="<?= htmlspecialchars($settings['nama_klinik'] ?? '') ?>" disabled>
                <small class="form-note">Nama klinik tidak dapat diubah</small>
            </div>
            
            <div class="form-group">
                <label for="alamat">Alamat <span class="required">*</span></label>
                <textarea class="form-control" id="alamat" name="alamat" rows="3" required><?= htmlspecialchars($settings['alamat'] ?? '') ?></textarea>
            </div>
            
            <div class="form-group">
                <label for="telepon">Telepon <span class="required">*</span></label>
                <input type="text" class="form-control" id="telepon" name="telepon" 
                       value="<?= htmlspecialchars($settings['telepon'] ?? '') ?>" required>
            </div>
            
            <div class="form-group">
                <label for="jam_operasional">Jam Operasional <span class="required">*</span></label>
                <input type="text" class="form-control" id="jam_operasional" name="jam_operasional" 
                       value="<?= htmlspecialchars($settings['jam_operasional'] ?? '') ?>" required>
            </div>
            
            <div class="btn-group">
                <button type="submit" name="update_klinik" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Perubahan
                </button>
                <button type="reset" class="btn btn-secondary" a href="../Dashboard/dashboard.php">
                    <i class="fas fa-undo"></i> Reset
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function togglePassword(fieldId) {
    const input = document.getElementById(fieldId);
    const icon = document.getElementById('icon-' + fieldId);
    const eyeIcon = icon.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        eyeIcon.classList.remove('fa-eye');
        eyeIcon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        eyeIcon.classList.remove('fa-eye-slash');
        eyeIcon.classList.add('fa-eye');
    }
}
</script>

</body>
</html>