<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Klinik Sehat</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ... CSS SAMA ... */
        * { margin: 0; padding: 0; box-sizing: border-box; }
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
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background: var(--white);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .login-container { max-width: 420px; width: 100%; }
        .login-card {
            background: var(--white);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .login-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            padding: 2.5rem 2rem;
            text-align: center;
            color: var(--white);
        }
        .logo {
            width: 64px;
            height: 64px;
            background: rgba(255,255,255,0.2);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }
        .login-body { padding: 2rem; }
        .form-group { margin-bottom: 1.5rem; }
        label {
            font-size: 0.875rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            display: block;
            color: var(--text-dark);
        }
        .input-group { position: relative; }
        .input-group i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-gray);
        }
        .form-control {
            width: 100%;
            padding: 0.875rem 1rem 0.875rem 2.75rem;
            border: 2px solid var(--border);
            border-radius: 10px;
            font-size: 0.875rem;
            transition: border-color 0.3s;
            /* TRIK 1: Background putih untuk hindari highlight autofill */
            background-color: var(--white) !important;
            /* TRIK 2: Override warna autofill Chrome */
            -webkit-box-shadow: 0 0 0 1000px white inset !important;
            box-shadow: 0 0 0 1000px white inset !important;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        .btn {
            width: 100%;
            padding: 0.875rem;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            display: flex;
            justify-content: center;
            gap: .5rem;
            transition: all 0.3s;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(37, 99, 235, 0.4);
        }
        .register-link {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 2px solid var(--light);
        }
        .register-link a {
            display: inline-block;
            margin-top: .75rem;
            padding: .75rem 1rem;
            border: 2px solid var(--primary);
            border-radius: 10px;
            text-decoration: none;
            color: var(--primary);
            font-weight: 600;
            transition: all 0.3s;
        }
        .register-link a:hover {
            background: var(--primary);
            color: white;
        }
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            text-align: center;
            font-weight: 600;
        }
        .alert-error {
            background: #fef2f2;
            color: #991b1b;
            border-left: 4px solid #ef4444;
        }
        .alert-success {
            background: #ecfdf5;
            color: #065f46;
            border-left: 4px solid #10b981;
        }
    </style>
</head>
<body>

<div class="login-container">
    <div class="login-card">
        <div class="login-header">
            <div class="logo"><i class="fas fa-hospital"></i></div>
            <h1>Klinik Sehat</h1>
            <p>Sistem Manajemen Klinik</p>
        </div>

        <div class="login-body">
            <h2>Selamat Datang</h2>
            <p style="margin-bottom:2rem;color:var(--text-gray);">Silakan login untuk melanjutkan</p>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-error">
                    <?php 
                    $errors = [
                        'kosong' => 'Email dan password wajib diisi!',
                        'email' => 'Email tidak terdaftar!',
                        'password' => 'Password salah!',
                        'invalid_session' => 'Sesi tidak valid, silakan login ulang',
                        'user_not_found' => 'User tidak ditemukan',
                        'access_denied' => 'Akses ditolak! Silakan login dengan akun yang sesuai'
                    ];
                    echo $errors[$_GET['error']] ?? 'Login gagal!';
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <?php 
                    $success_msg = [
                        'logout' => 'Anda berhasil logout!',
                        'account_deleted' => 'Akun berhasil dihapus. Silakan daftar ulang jika ingin menggunakan layanan.'
                    ];
                    echo $success_msg[$_GET['success']] ?? 'Berhasil!';
                    ?>
                </div>
            <?php endif; ?>

            <!-- TRIK 3: Form dengan autocomplete="new-password" (trick browser) -->
            <form action="proses_login.php" method="POST" autocomplete="off">
                <div class="form-group">
                    <label for="email">Email</label>
                    <div class="input-group">
                        <i class="fas fa-envelope"></i>
                        <!-- TRIK 4: readonly + onfocus remove readonly + autocomplete="username" -->
                        <input type="email" class="form-control" id="email" name="email" 
                               required 
                               autocomplete="username"
                               readonly 
                               onfocus="this.removeAttribute('readonly'); this.focus();">
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-group">
                        <i class="fas fa-lock"></i>
                        <!-- TRIK 5: autocomplete="new-password" untuk password field -->
                        <input type="password" class="form-control" id="password" name="password" 
                               required 
                               autocomplete="new-password"
                               readonly 
                               onfocus="this.removeAttribute('readonly'); this.focus();">
                    </div>
                </div>

                <button type="submit" class="btn">
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>

            <div class="register-link">
                <p>Belum punya akun?</p>
                <a href="register.php">
                    <i class="fas fa-user-plus"></i> Daftar Sebagai Pasien
                </a>
            </div>
        </div>
    </div>
</div>

<!-- TRIK 6: JavaScript clear fields (last resort) -->
<script>
    // Clear fields on page load
    document.addEventListener('DOMContentLoaded', function() {
        const email = document.getElementById('email');
        const password = document.getElementById('password');
        
        // Force clear values
        email.value = '';
        password.value = '';
        
        // Prevent browser autofill from reappearing
        setTimeout(() => {
            email.value = '';
            password.value = '';
        }, 100);
    });
</script>

</body>
</html>