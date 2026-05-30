<?php
// app/helpers/functions.php
// ============================================================
// Fungsi Utility Global
// ============================================================

/**
 * Redirect ke URL tertentu
 */
function redirect(string $url): void {
    header("Location: " . APP_URL . $url);
    exit;
}

/**
 * Sanitize output untuk mencegah XSS
 */
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Ambil nilai POST dengan fallback
 */
function post(string $key, mixed $default = ''): mixed {
    return $_POST[$key] ?? $default;
}

/**
 * Ambil nilai GET dengan fallback
 */
function get(string $key, mixed $default = ''): mixed {
    return $_GET[$key] ?? $default;
}

/**
 * Flash message: simpan ke session
 */
function flash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Ambil dan hapus flash message
 */
function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Format tanggal ke format Indonesia
 */
function formatDate(string $date): string {
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    $ts = strtotime($date);
    return date('d', $ts) . ' ' . $bulan[(int)date('n', $ts)] . ' ' . date('Y', $ts);
}

/**
 * Truncate teks dengan ellipsis
 */
function truncate(string $text, int $length = 150): string {
    $plain = strip_tags($text);
    if (mb_strlen($plain) <= $length) return $plain;
    return mb_substr($plain, 0, $length) . '...';
}

/**
 * Generate CSRF token
 */
function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validasi CSRF token
 */
function verifyCsrf(): void {
    $token = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(403);
        die('CSRF token mismatch.');
    }
}

/**
 * Hitung estimasi waktu baca
 */
function readingTime(string $content): string {
    $words = str_word_count(strip_tags($content));
    $minutes = ceil($words / 200); // rata-rata 200 kata/menit
    return $minutes . ' menit baca';
}

/**
 * Buat excerpt bersih dari HTML content
 */
function makeExcerpt(string $html, int $length = 180): string {
    return truncate(strip_tags(html_entity_decode($html)), $length);
}

/**
 * Slug generator
 */
function slugify(string $text): string {
    $text = mb_strtolower($text, 'UTF-8');
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', trim($text));
    return $text;
}
