<?php
// Session Configuration and Initialization

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0, // penting
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax' 
    ]);

    session_start();
}

// Check session timeout
if (isset($_SESSION['last_activity'])) {
    if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
        session_destroy();
        header('Location: ' . BASE_URL . '/login.php?session=expired');
        exit();
    }
}

$_SESSION['last_activity'] = time();

// Store IP for security
if (!isset($_SESSION['ip_address'])) {
    $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'];
} elseif ($_SESSION['ip_address'] !== $_SERVER['REMOTE_ADDR']) {
    // Possible session hijacking, log out user
    session_destroy();
    header('Location: ' . BASE_URL . '/login.php?session=invalid');
    exit();
}
