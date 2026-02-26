<?php
// Utility Functions

function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)), ENT_QUOTES, 'UTF-8');
}

function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function hash_password($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

function verify_password($password, $hash) {
    return password_verify($password, $hash);
}

function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function is_logged_in() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit();
    }
}

function require_admin() {
    require_login();
    if ($_SESSION['user_role'] !== 'admin') {
        http_response_code(403);
        die('Access Denied');
    }
}

function require_cashier() {
    require_login();
    if (!in_array($_SESSION['user_role'], ['cashier', 'admin'])) {
        http_response_code(403);
        die('Access Denied');
    }
}

function json_response($data, $statusCode = 200) {
    header('Content-Type: application/json');
    http_response_code($statusCode);
    echo json_encode($data);
    exit();
}

function redirect($url) {
    header('Location: ' . $url);
    exit();
}

function format_currency($amount) {
    return '$' . number_format($amount, 2);
}

function format_date($date, $format = 'M d, Y') {
    return date($format, strtotime($date));
}

function generate_receipt_number() {
    return '#' . strtoupper(uniqid('RCP'));
}

function log_activity($pdo, $user_id, $action, $details = null) {
    $stmt = $pdo->prepare("
        INSERT INTO activity_log (user_id, action, details, created_at)
        VALUES (?, ?, ?, NOW())
    ");
    return $stmt->execute([$user_id, $action, $details]);
}
