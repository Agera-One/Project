<?php

require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/utils.php';

if (is_logged_in()) {
    if ($_SESSION['user_role'] === 'admin') {
        header('Location: /admin/dashboard.php');
    } else {
        header('Location: /cashier/index.php');
    }
    exit();
}

header('Location: /login.php');
exit();