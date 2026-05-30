<?php
// app/controllers/AuthController.php
// ============================================================
// Controller Authentication: Login & Register
// ============================================================

class AuthController {
    private User $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    // ---- GET /auth/login ----
    public function showLogin(): void {
        AuthMiddleware::redirectIfLoggedIn();
        include dirname(__DIR__, 2) . '/views/auth/login.php';
    }

    // ---- POST /auth/login ----
    public function login(): void {
        AuthMiddleware::redirectIfLoggedIn();
        verifyCsrf();

        $username = trim(post('username'));
        $password = post('password');

        // Validasi input
        if (empty($username) || empty($password)) {
            flash('error', 'Username dan password wajib diisi.');
            redirect('/auth/login');
        }

        $user = $this->userModel->findByUsername($username);

        if (!$user || !$this->userModel->verifyPassword($password, $user)) {
            flash('error', 'Username atau password salah.');
            redirect('/auth/login');
        }

        loginUser($user);
        flash('success', 'Selamat datang, ' . e($user['username']) . '!');

        // Role-based redirect
        if ($user['role'] === 'admin') {
            redirect('/admin/dashboard');
        } else {
            redirect('/articles');
        }
    }

    // ---- GET /auth/register ----
    public function showRegister(): void {
        AuthMiddleware::redirectIfLoggedIn();
        include dirname(__DIR__, 2) . '/views/auth/register.php';
    }

    // ---- POST /auth/register ----
    public function register(): void {
        AuthMiddleware::redirectIfLoggedIn();
        verifyCsrf();

        $username = trim(post('username'));
        $password = post('password');
        $confirm  = post('confirm_password');

        // Validasi
        $errors = [];
        if (empty($username)) $errors[] = 'Username wajib diisi.';
        if (strlen($username) < 3) $errors[] = 'Username minimal 3 karakter.';
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) $errors[] = 'Username hanya boleh huruf, angka, dan underscore.';
        if (empty($password)) $errors[] = 'Password wajib diisi.';
        if (strlen($password) < 8) $errors[] = 'Password minimal 8 karakter.';
        if ($password !== $confirm) $errors[] = 'Konfirmasi password tidak cocok.';

        if ($errors) {
            flash('error', implode('<br>', $errors));
            redirect('/auth/register');
        }

        $success = $this->userModel->register($username, $password);

        if (!$success) {
            flash('error', 'Username sudah digunakan. Pilih username lain.');
            redirect('/auth/register');
        }

        flash('success', 'Registrasi berhasil! Silakan login dengan akun baru kamu.');
        redirect('/auth/login');
    }

    // ---- GET /auth/logout ----
    public function logout(): void {
        logoutUser();
        flash('success', 'Kamu telah berhasil logout.');
        redirect('/auth/login');
    }
}
