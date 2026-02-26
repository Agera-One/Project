<?php
require_once '../config/database.php';
require_once '../config/utils.php';
require_once '../config/session.php';

require_login();

// Log activity
log_activity($pdo, $_SESSION['user_id'], 'logout', 'User logged out');

// Destroy session
$_SESSION = [];
if (session_id() !== '') {
    setcookie(session_name(), '', [
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
}
session_destroy();

// Redirect to login
redirect('../login.php');
