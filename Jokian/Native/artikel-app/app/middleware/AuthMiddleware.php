<?php
// app/middleware/AuthMiddleware.php
// ============================================================
// Middleware untuk proteksi route
// ============================================================

class AuthMiddleware {

    /**
     * Wajib login — redirect ke login page jika belum
     */
    public static function requireLogin(): void {
        if (!isLoggedIn()) {
            flash('error', 'Silakan login terlebih dahulu untuk mengakses halaman ini.');
            redirect('/auth/login');
        }
    }

    /**
     * Wajib admin — redirect jika bukan admin
     */
    public static function requireAdmin(): void {
        self::requireLogin();
        if (!isAdmin()) {
            http_response_code(403);
            include dirname(__DIR__, 2) . '/views/errors/403.php';
            exit;
        }
    }

    /**
     * Redirect jika sudah login (untuk halaman login/register)
     */
    public static function redirectIfLoggedIn(): void {
        if (isLoggedIn()) {
            if (isAdmin()) {
                redirect('/admin/dashboard');
            } else {
                redirect('/articles');
            }
        }
    }
}
