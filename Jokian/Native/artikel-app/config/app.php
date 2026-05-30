<?php
// config/app.php
// ============================================================
// Konfigurasi Aplikasi Global
// ============================================================

// --- Environment ---
define('APP_ENV', 'development'); // 'development' | 'production'
define('APP_NAME', 'ArtikelHub');
define('APP_URL', 'http://localhost:8000');
define('APP_VERSION', '1.0.0');

// --- Session ---
define('SESSION_NAME', 'artikel_session');
define('SESSION_LIFETIME', 7200); // 2 jam dalam detik

// --- Security ---
define('JWT_SECRET', 'GANTI_DENGAN_SECRET_PANJANG_DAN_ACAK_MINIMAL_32_KARAKTER');
define('BCRYPT_COST', 12);

// --- Pagination ---
define('ARTICLES_PER_PAGE', 9);
define('ADMIN_PER_PAGE', 10);

// --- Upload ---
define('UPLOAD_PATH', dirname(__DIR__) . '/uploads/');
define('MAX_FILE_SIZE', 2 * 1024 * 1024); // 2MB

// --- Display Errors (sesuaikan APP_ENV) ---
if (APP_ENV === 'development') {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    error_reporting(0);
}

// --- Inisialisasi Session ---
ini_set('session.name', SESSION_NAME);
ini_set('session.cookie_lifetime', SESSION_LIFETIME);
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// --- Autoload semua konfigurasi & core ---
require_once __DIR__ . '/database.php';
require_once dirname(__DIR__) . '/app/helpers/functions.php';
require_once dirname(__DIR__) . '/app/helpers/auth_helper.php';
require_once dirname(__DIR__) . '/app/models/User.php';
require_once dirname(__DIR__) . '/app/models/Article.php';
require_once dirname(__DIR__) . '/app/controllers/AuthController.php';
require_once dirname(__DIR__) . '/app/controllers/ArticleController.php';
require_once dirname(__DIR__) . '/app/controllers/AdminController.php';
require_once dirname(__DIR__) . '/app/middleware/AuthMiddleware.php';
