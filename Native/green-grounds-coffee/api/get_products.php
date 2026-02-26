<?php
require_once '../config/database.php';
require_once '../config/utils.php';
require_once '../config/session.php';

require_cashier();

header('Content-Type: application/json');

$category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;

try {
    if ($category_id) {
        $stmt = $pdo->prepare("
            SELECT p.* FROM products p
            WHERE p.category_id = ? AND p.status = 'available'
            ORDER BY p.name ASC
        ");
        $stmt->execute([$category_id]);
    } else {
        $stmt = $pdo->query("
            SELECT * FROM products
            WHERE status = 'available'
            ORDER BY name ASC
        ");
    }

    $products = $stmt->fetchAll();
    json_response(['success' => true, 'products' => $products]);
} catch (PDOException $e) {
    json_response(['success' => false, 'error' => $e->getMessage()], 500);
}
