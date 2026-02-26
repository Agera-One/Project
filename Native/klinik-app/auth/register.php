<?php
$adminError = isset($_GET['error']) && $_GET['error'] === 'admin';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Pasien - Klinik Sehat</title>
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
            --danger: #ef4444;
            --light: #f8fafc;
            --white: #ffffff;
            --text-dark: #1e293b;
            --text-gray: #64748b;
            --border: #e2e8f0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--white);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .register-container {
            width: 100%;
            max-width: 550px;
        }

        .register-card {
            background: var(--white);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }

        .register-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            padding: 2rem;
            text-align: center;
            color: var(--white);
        }

        .register-header .logo {
            width: 56px;
            height: 56px;
            background: rgba(255,255,255,0.2);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }

        .register-body {
            padding: 2rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        label {
            font-size: 0.8125rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            display: block;
            color: var(--text-dark);
        }

        .required {
            color: var(--danger);
        }

        .input-group {
            position: relative;
        }

        .input-group i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-gray);
            font-size: 0.875rem;
        }

        .form-control {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.75rem;
            border: 2px solid var(--border);
            border-radius: 8px;
            font-size: 0.875rem;
        }

        textarea.form-control {
            min-height: 80px;
        }

        .btn {
            width: 100%;
            padding: 0.875rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            margin-top: 1rem;
        }

        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 2px solid var(--light);
        }

        .login-link a {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
        }

        @media (max-width: 640px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>

<div class="register-container">
    <div class="register-card">
        <div class="register-header">
            <div class="logo">
                <i class="fas fa-user-plus"></i>
            </div>
            <h1>Daftar Pasien Baru</h1>
            <p>Isi formulir di bawah untuk membuat akun</p>
        </div>

        <div class="register-body">
            <form action="../auth/proses_register.php" method="POST">
                <div class="form-row">

                    <div class="form-group full-width">
                        <label>Nama Lengkap <span class="required">*</span></label>
                        <div class="input-group">
                            <i class="fas fa-user"></i>
                            <input type="text" class="form-control" name="nama" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>NIK <span class="required">*</span></label>
                        <div class="input-group">
                            <i class="fas fa-id-card"></i>
                            <input type="text" class="form-control" name="nik" maxlength="16" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Tanggal Lahir <span class="required">*</span></label>
                        <div class="input-group">
                            <i class="fas fa-calendar"></i>
                            <input type="date" class="form-control" name="tgl_lahir" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>No. HP <span class="required">*</span></label>
                        <div class="input-group">
                            <i class="fas fa-phone"></i>
                            <input type="tel" class="form-control" name="no_hp" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Email <span class="required">*</span></label>
                        <div class="input-group">
                            <i class="fas fa-envelope"></i>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label>Alamat <span class="required">*</span></label>
                        <div class="input-group">
                            <i class="fas fa-map-marker-alt"></i>
                            <textarea class="form-control" name="alamat" required></textarea>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Password <span class="required">*</span></label>
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" class="form-control" name="password" placeholder="Minimal 6 karakter" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Konfirmasi Password <span class="required">*</span></label>
                        <div class="input-group">
                            <i class="fas fa-lock"></i>
                            <input type="password" class="form-control" name="confirm_password" placeholder="Ulangi password" required>
                        </div>
                    </div>

                </div>

                <button type="submit" class="btn">
                    <i class="fas fa-user-plus"></i> Daftar Sekarang
                </button>
            </form>

            <div class="login-link">
                <p>Sudah punya akun? <a href="../auth/login.php">Login di sini</a></p>
            </div>
        </div>
    </div>
</div>

<?php 
if (isset($_GET['error'])): ?>
<script>
let pesan = {
    kosong: "Data belum lengkap!",
    password: "Password tidak sama!",
    pendek: "Password minimal 6 karakter!",
    admin: "Akun admin tidak boleh mendaftar!",
    terdaftar: "Email sudah terdaftar!",
    gagal: "Registrasi gagal, coba lagi."
};

alert(pesan["<?= $_GET['error']; ?>"] || "Terjadi kesalahan");
document.querySelector("form").reset();
</script>
<?php endif; 
?>

</body>
</html>
